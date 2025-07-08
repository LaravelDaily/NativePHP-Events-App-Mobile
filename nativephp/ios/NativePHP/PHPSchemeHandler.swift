import WebKit

class PHPSchemeHandler: NSObject, WKURLSchemeHandler {
    let domain = "127.0.0.1"

    var redirectCount = 0
    let maxRedirects = 10

    private let phpSerialQueue: DispatchQueue

    override init() {
        let appName = Bundle.main.infoDictionary?["CFBundleName"] as? String ?? "DefaultAppName"
        let queueLabel = "com.NativePHP.\(appName).phpSerialQueue"
        self.phpSerialQueue = DispatchQueue(label: queueLabel)
    }

    // This method is called when the web view starts loading a request with your custom scheme
    func webView(_ webView: WKWebView, start schemeTask: WKURLSchemeTask) {
        startLoading(for: schemeTask)
    }

    // This method is called if the web view stops loading the request
    func webView(_ webView: WKWebView, stop schemeTask: WKURLSchemeTask) {
        // Implement if you need to handle task cancellation
        stopLoading(for: schemeTask)
    }

    // This method is called when a request with the custom scheme is made
    func startLoading(for schemeTask: WKURLSchemeTask) {
        guard let request = schemeTask.request as URLRequest?,
              let url = request.url else {
            let error = error(code: 400, description: "Invalid request")
            schemeTask.didFailWithError(error)
            return
        }

        // Extract request data
        extractRequestData(from: request) { [weak self] result in
            switch result {
            case .success(let requestData):
                let pathComponents = url.pathComponents

                if let assetsIndex = pathComponents.firstIndex(of: "_assets") {
                    // Gather everything after "_assets":
                    let subComponents = pathComponents[(assetsIndex + 1)...]

                    // Join them back together: "build/app.js"
                    let relativeAssetPath = subComponents.joined(separator: "/")

                    // Attempt to find this file in app/public
                    let assetDir = "app/public"

                    // Combine that with the subpath.
                    if let localPath = Bundle.main.path(forResource: relativeAssetPath,
                                                        ofType: nil,
                                                        inDirectory: assetDir) {

                        do {
                            let fileData = try Data(contentsOf: URL(fileURLWithPath: localPath))

                            let mimeType = self!.guessMimeType(for: relativeAssetPath)

                            let response = HTTPURLResponse(url: url,
                                                           statusCode: 200,
                                                           httpVersion: "HTTP/1.1",
                                                           headerFields: ["Content-Type": mimeType])

                            schemeTask.didReceive(response!)
                            schemeTask.didReceive(fileData)
                            schemeTask.didFinish()

                            return
                        } catch {
                            // Just fall back to PHP
                        }
                    }
                }

                WebView.dataStore.httpCookieStore.getAllCookies { cookies in
                    var request = requestData

                    let domainCookies = cookies.filter { $0.domain == "127.0.0.1" }

                    var csrfToken: String = "";

                    // Build "Cookie" header
                    let cookieHeader = domainCookies.map {
                        if ($0.name == "XSRF-TOKEN") {
                            csrfToken = $0.value.removingPercentEncoding ?? ""
                        }

                        return "\($0.name)=\($0.value.removingPercentEncoding ?? "")"
                    }.joined(separator: "; ")

                    request.headers["Cookie"] = cookieHeader
                    request.headers["X-XSRF-TOKEN"] = csrfToken

                    self!.forwardToPHP(requestData: request, schemeTask: schemeTask)
                }

            case .failure(let error):
                // Pass the extraction error back to the scheme task
                schemeTask.didFailWithError(error)
            }
        }
    }

    func stopLoading(for schemeTask: WKURLSchemeTask) {
        // Handle stopping the loading if needed
    }

    private func guessMimeType(for fileName: String) -> String {
        let pathExtension = (fileName as NSString).pathExtension.lowercased()
        switch pathExtension {
        case "html", "htm":
            return "text/html"
        case "css":
            return "text/css"
        case "js":
            return "application/javascript"
        case "png":
            return "image/png"
        case "jpg", "jpeg":
            return "image/jpeg"
        case "gif":
            return "image/gif"
        case "svg":
            return "image/svg+xml"
        default:
            return "application/octet-stream"
        }
    }

    // Helper method to extract request data
    private func extractRequestData(from request: URLRequest,
                                    completion: @escaping (Result<RequestData, Error>) -> Void) {
        guard request.url?.host == domain else {
            // If the domain doesn't match, don't do anything
            print("⚠ Domain doesn't match expected!")
            print(request.url?.host ?? "")
            return
        }

        // Extract GET parameters
        var query: String?
        if let url = request.url {
            let urlComponents = URLComponents(url: url, resolvingAgainstBaseURL: false)
            query = urlComponents?.query
        }

        // Extract HTTP method
        let method = request.httpMethod ?? "GET"

        // Extract Headers
        let headers = request.allHTTPHeaderFields ?? [:]

        // Extract POST data if method is POST/PUT/PATCH
        var data: String?
        if ["POST", "PUT", "PATCH"].contains(method.uppercased()), let httpBody = request.httpBody {
            if let body = String(data: httpBody, encoding: .utf8) {
                data = body
            }
        }

        // Define the URI
        let uri = request.url?.path ?? "/"

        // Create a RequestData object
        let requestData = RequestData(
            method: method,
            uri: uri,
            data: data ?? nil,
            query: query ?? "",
            headers: headers
        )

        // Pass the extracted data back via completion
        completion(.success(requestData))
    }

    private func parseSetCookieHeader(cookieString: String) -> [HTTPCookiePropertyKey: Any] {
        var properties: [HTTPCookiePropertyKey: Any] = [:]

        // Split the cookie string into components separated by ';'
        let components = cookieString.split(separator: ";")

        // The first component is "name=value"
        if let nameValue = components.first {
            let nv = nameValue.split(separator: "=", maxSplits: 1)
            if nv.count == 2 {
                let name = String(nv[0])
                let value = String(nv[1])
                properties[.name] = name
                properties[.value] = value
            }
        }

        // The remaining components are attributes
        for attribute in components.dropFirst() {
            let attr = attribute.trimmingCharacters(in: .whitespacesAndNewlines)
            let pair = attr.split(separator: "=", maxSplits: 1)
            if pair.count == 2 {
                let key = String(pair[0]).lowercased()
                let value = String(pair[1])
                switch key {
                case "path":
                    properties[.path] = value
                case "domain":
                    properties[.domain] = value
                case "expires":
                    let dateFormatter = DateFormatter()
                    dateFormatter.locale = Locale(identifier: "en_US_POSIX")
                    dateFormatter.dateFormat = "E, d MMM yyyy HH:mm:ss z"
                    if let date = dateFormatter.date(from: value) {
                        properties[.expires] = date
                    }
                case "httponly":
                    properties[.setByJavaScript] = false
                case "secure":
                    properties[.secure] = true
                default:
                    break
                }
            } else {
                // Attributes like 'HttpOnly' or 'Secure' without value
                let key = String(pair[0]).lowercased()
                if key == "httponly" {
                    properties[.setByJavaScript] = false
                } else if key == "secure" {
                    properties[.secure] = true
                }
            }
        }

        // Set the domain and path if not already set
        if properties[.domain] == nil {
            properties[.domain] = domain
        }

        if properties[.path] == nil {
            properties[.path] = "/"
        }

        return properties
    }

    private func error(code: Int, description: String) -> NSError
    {
        print("ERROR: \(description)")
        return NSError(domain: "PHPAppSchemeHandler", code: code, userInfo: [NSLocalizedDescriptionKey: description])
    }

    private func forwardToPHP(requestData: RequestData, schemeTask: WKURLSchemeTask) {
        getResponse(request: requestData) { result in
            switch result {
            case .success(let responseData):
                // Parse the response data into headers and body
                guard let responseString = String(data: responseData, encoding: .utf8) else {
                    let error = self.error(code: 500, description: "Failed to decode response")
                    schemeTask.didFailWithError(error)
                    return
                }

                // Split headers and body
                print("Processing response...")
                let components = responseString.components(separatedBy: "\r\n\r\n")
                guard components.count >= 2 else {
                    // Send the error as a response to the WebView
                    guard let httpResponse = HTTPURLResponse(url: URL(string: requestData.uri)!,
                                                             statusCode: 500,
                                                             httpVersion: "HTTP/1.1",
                                                             headerFields: [
                                                                "Content-Type": "text/html",
                                                                "Content-Length": "\(components[0].lengthOfBytes(using: .utf8))"
                                                             ]) else {
                        let error = self.error(code: 500, description: "Failed to create HTTP response")
                        schemeTask.didFailWithError(error)
                        return
                    }

                    schemeTask.didReceive(httpResponse)

                    if let data = components[0].data(using: .utf8) {
                        schemeTask.didReceive(data)
                    }

                    _ = self.error(code: 500, description: "Invalid PHP Response Format")
                    schemeTask.didFinish()

                    return
                }

                let headerString = components[0]
                let bodyString = components[1]

                // Parse headers into a dictionary
                var headers: [String: String] = [:]
                let headerLines = headerString.components(separatedBy: "\r\n")

                for (index, line) in headerLines.enumerated() {
                    // First one is status, which we'll parse out separately
                    if index == 0 {
                        continue
                    }
                    let headerComponents = line.components(separatedBy: ": ")
                    if headerComponents.count == 2 {
                        headers[headerComponents[0]] = headerComponents[1]
                    }
                }

                print()
                print(headerLines.first ?? "")

                // Determine the status code (default to 200)
                var statusCode = 200
                if let statusLine = headerLines.first,
                   let codeString = statusLine.components(separatedBy: " ").dropFirst(1).first,
                   let code = Int(codeString) {
                    statusCode = code
                }

                var request = requestData
                if let location = headers["Location"] {
                    request.uri = location.trimmingCharacters(in: .whitespaces)
                    request.method = "GET"

                    // Perform an external redirect to the webview, not trying to pass the location to PHP again
                    if !request.uri.hasPrefix("http://") && !request.uri.hasPrefix("php://") {
                        NotificationCenter.default.post(name: .redirectToURLNotification, object: nil, userInfo: ["url": location.trimmingCharacters(in: .whitespaces)])
                        return
                    }

                    WebView.dataStore.httpCookieStore.getAllCookies { cookies in
                        let domainCookies = cookies.filter { $0.domain == "127.0.0.1" }

                        // Build "Cookie" header
                        let cookieHeader = domainCookies.map {
                            return "\($0.name)=\($0.value.removingPercentEncoding ?? "")"
                        }.joined(separator: "; ")

                        request.headers["Cookie"] = cookieHeader

                        self.redirectCount += 1

                        if self.redirectCount > self.maxRedirects {
                            let error = self.error(code: 500, description: "Too Many Redirects")
                            schemeTask.didFailWithError(error)
                            return
                        }

                        self.forwardToPHP(requestData: request, schemeTask: schemeTask)
                    }

                    return
                }

                self.redirectCount = 0

                print("Forwarding response to WebView")

                guard let httpResponse = HTTPURLResponse(url: (URL(string: requestData.uri) ?? URL(string: "/"))!,
                                                        statusCode: statusCode,
                                                        httpVersion: "HTTP/1.1",
                                                        headerFields: headers) else {
                    let error = self.error(code: 500, description: "Failed to create HTTP response")
                    schemeTask.didFailWithError(error)
                    return
                }

                // Send the response to the task
                schemeTask.didReceive(httpResponse)

                // Send the body data
                if let bodyData = bodyString.data(using: .utf8) {
                    schemeTask.didReceive(bodyData)
                } else {
                    let error = self.error(code: 500, description: "Failed to encode body data")
                    schemeTask.didFailWithError(error)
                    return
                }

                // Indicate that the task has finished
                schemeTask.didFinish()
                print("Done")

            case .failure(let error):
                // Handle failure by sending the error to the task
                schemeTask.didFailWithError(error)
            }
        }
    }

    private func getResponse(request: RequestData,
                              completion: @escaping (Result<Data, Error>) -> Void) {
        phpSerialQueue.async {
            print()
            print("\(request.method) \(request.uri)")
            print()
            print(request.headers.map { "\($0.key)=\($0.value)" }.joined(separator: "\n"))

            // Pass the request to Laravel and get Laravel's response
            let response = NativePHPApp.laravel(request: request) ?? "No response from Laravel"

            // Extract cookie headers
            let components = response.components(separatedBy: "\r\n\r\n")
            let headers = components[0]

            let headersList = headers.components(separatedBy: "\n").filter { !$0.isEmpty }

            let setCookieHeaders = headersList.filter { $0.hasPrefix("Set-Cookie:") }

            DispatchQueue.main.async {
                for header in setCookieHeaders {
                    // Remove "Set-Cookie: " prefix
                    let cookieString = header.replacingOccurrences(of: "Set-Cookie: ", with: "")
                        .trimmingCharacters(in: .whitespacesAndNewlines)
                        .replacingOccurrences(of: ";\\s+", with: ";", options: .regularExpression)

                    // Create HTTPCookie from the cookieString
                    if let cookie = HTTPCookie(properties: self.parseSetCookieHeader(cookieString: cookieString)) {
                        // Set the cookie in WKHTTPCookieStore
                        WebView.dataStore.httpCookieStore.setCookie(cookie)
                    }
                }

                // Convert the response to Data
                if let responseData = response.data(using: .utf8) {
                    completion(.success(responseData))
                } else {
                    let encodingError = self.error(code: 500, description: "Failed to encode PHP response")
                    completion(.failure(encodingError))
                }
            }
        }
    }
}

struct RequestData {
    var method: String
    var uri: String
    var data: String?
    var query: String?
    var headers: [String: String]
}
