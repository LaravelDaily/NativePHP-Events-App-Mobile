import SwiftUI
import WebKit

struct ContentView: View {
    @State private var phpOutput = ""
    @State private var showDebugView: Bool = false

    var body: some View {
        WebView()
            .edgesIgnoringSafeArea(.all)
            .confirmationDialog(
                "NativePHP Debug View",
                isPresented: $showDebugView
            ) {
                Button("Reload") {
                    NotificationCenter.default.post(name: .reloadWebViewNotification, object: nil)
                }
                Button("Cancel", role: .cancel) {}
            }
            .onReceive(NotificationCenter.default.publisher(for: .deviceDidShakeNotification)) { _ in
            #if DEBUG
                showDebugView = true
            #endif
            }
    }
}

struct WebView: UIViewRepresentable {
    static let dataStore = WKWebsiteDataStore.nonPersistent()

    let webView: WKWebView

    func makeCoordinator() -> Coordinator {
        Coordinator(self)
    }

    static func dismantleUIView(_ uiView: WKWebView, coordinator: Coordinator) {
        LaravelBridge.shared.send = nil
    }

    class Coordinator: NSObject, WKNavigationDelegate {
        let logger = ConsoleLogger()

        let parent: WebView

        init(_ parent: WebView) {
            self.parent = parent
        }

        func webView(_ webView: WKWebView,
                     decidePolicyFor navigationAction: WKNavigationAction,
                     decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
            guard let url = navigationAction.request.url else {
                decisionHandler(.allow)
                return
            }

            // Intercept normal http/https links and open them in system default browser
            if url.scheme == "http" || url.scheme == "https" {
                UIApplication.shared.open(url)
                decisionHandler(.cancel)
            } else {
                decisionHandler(.allow)
            }
        }

        @MainActor
        func notifyLaravel(
            event: String,
            payload: [String: Any]
        ) {
            let event: String = {
                let data = try! JSONSerialization.data(withJSONObject: [event])
                var literal = String(data: data, encoding: .utf8)!
                literal.removeFirst()
                literal.removeLast()
                return literal
            }()

            // 1. Inject JS event into the current web page
            if let jsonData = try? JSONSerialization.data(withJSONObject: payload, options: []),
               let jsonString = String(data: jsonData, encoding: .utf8) {

                let js = """
                (function() {
                    const event = new CustomEvent(
                        "native-event",
                        {
                            detail: {
                                event: \(event),
                                payload: \(jsonString),
                            },
                        }
                    );
                    document.dispatchEvent(event);

                    fetch('/_native/api/events', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            event: \(event),
                            payload: \(jsonString),
                        })
                    }).then(response => response.json())
                      .then(data => console.log("API Event Dispatch Success:", JSON.stringify(data, null, 2)))
                      .catch(error => console.error("API Event Dispatch Error:", error));
                })();
                """

                parent.webView.evaluateJavaScript(js) { result, error in
                    if let error = error {
                        print("JavaScript injection error injecting event '\(event)': \(error)")
                    } else {
                        print("JavaScript event '\(event)' dispatched.")
                    }
                }

                // FUTURE: Send a request to Laravel backend directly
//                let request = RequestData(
//                    method: "POST",
//                    uri: "php://127.0.0.1/_native/api/events",
//                    data: jsonString,
//                    headers: [
//                        "Content-Type": "application/json"
//                    ])
//
//                _ = NativePHPApp.laravel(request: request)

            }
        }

        @objc func reloadWebView() {
            self.parent.webView.reload()
        }

        @objc func handleSwipeLeft(_ gesture: UISwipeGestureRecognizer) {
            if let webView = gesture.view as? WKWebView, webView.canGoForward {
                webView.goForward()
            }
        }

        @objc func handleSwipeRight(_ gesture: UISwipeGestureRecognizer) {
            if let webView = gesture.view as? WKWebView, webView.canGoBack {
                webView.goBack()
            }
        }

        @objc func redirectToURL(_ notification: Notification) {
            DebugLogger.shared.log("🌐 WebView received redirect notification")
            DebugLogger.shared.log("🌐 WebView current state - isLoading: \(self.parent.webView.isLoading), URL: \(self.parent.webView.url?.absoluteString ?? "nil")")
            
            if let urlString = notification.userInfo?["url"] as? String {
                DebugLogger.shared.log("🌐 WebView redirecting to: \(urlString)")
                if let url = URL(string: urlString) {
                    // Stop any current loading before starting new request
                    if self.parent.webView.isLoading {
                        DebugLogger.shared.log("🌐 WebView stopping current load before redirect")
                        self.parent.webView.stopLoading()
                    }
                    
                    DebugLogger.shared.log("🌐 WebView calling load() with URL")
                    self.parent.webView.load(URLRequest(url: url))
                    DebugLogger.shared.log("🌐 WebView load() call completed")
                } else {
                    DebugLogger.shared.log("🌐 WebView failed to create URL from: \(urlString)")
                }
            } else {
                DebugLogger.shared.log("🌐 WebView notification missing URL data")
            }
        }
    }

    init() {
        // Initialize the custom scheme handler
        let schemeHandler = PHPSchemeHandler()

        // Configure WKWebView with the custom scheme handler
        let webConfiguration = WKWebViewConfiguration()

        webConfiguration.websiteDataStore = WebView.dataStore
        webConfiguration.setURLSchemeHandler(schemeHandler, forURLScheme: "php")

        webView = WKWebView(frame: .zero, configuration: webConfiguration)
    }

    func makeUIView(context: Context) -> WKWebView {
        let coordinator = context.coordinator

        LaravelBridge.shared.send = { [weak coordinator] event, payload in
            Task { @MainActor in
                coordinator?.notifyLaravel(event: event, payload: payload)
            }
        }

        addDebugSupport(context: context)

        addNativeHelper()

        addSwipeGestureSupport(context: context)

        NotificationCenter.default.addObserver(
            context.coordinator,
            selector: #selector(Coordinator.reloadWebView),
            name: .reloadWebViewNotification,
            object: nil
        )

        NotificationCenter.default.addObserver(
            context.coordinator,
            selector: #selector(Coordinator.redirectToURL),
            name: .redirectToURLNotification,
            object: nil
        )

        webView.scrollView.bounces = false

        let fallbackPath = Bundle.main.path(forResource: "index", ofType: "html")
        let fallbackURL = URL(fileURLWithPath: fallbackPath!)

        // Give AppDelegate time to process any launch deep links before deciding what to load
        DispatchQueue.main.asyncAfter(deadline: .now() + 0.1) {
            DebugLogger.shared.log("🌐 WebView setup after AppDelegate delay")
            
            // Mark WebView as ready
            DeepLinkRouter.shared.markWebViewReady()
            
            // Only load default URL if no pending deep link
            if !DeepLinkRouter.shared.hasPendingURL() {
                DebugLogger.shared.log("🌐 No pending deep link, loading default URL")
                let startPage = URL(string: "php://127.0.0.1/")
                webView.load(URLRequest(url: startPage ?? fallbackURL))
            } else {
                DebugLogger.shared.log("🌐 Pending deep link detected, skipping default URL load")
            }
        }

        return webView
    }

    func addDebugSupport(context: Context) {
        #if DEBUG
        let userContentController = webView.configuration.userContentController
        let consoleLoggingScript = """
        (function() {
            function capture(type) {
                var old = console[type];
                console[type] = function() {
                    var message = Array.prototype.slice.call(arguments).join(" ");
                    window.webkit.messageHandlers.console.postMessage({ type: type, message: message });
                    old.apply(console, arguments);
                };
            }
            ['log', 'warn', 'error', 'debug'].forEach(capture);
        })();
        """

        let userScript = WKUserScript(source: consoleLoggingScript, injectionTime: .atDocumentStart, forMainFrameOnly: false)
        userContentController.addUserScript(userScript)
        userContentController.add(context.coordinator.logger, name: "console")

        webView.isInspectable = true
        #endif
    }

    func addNativeHelper() {
        let helper = """
        const Native = {
            on: (event, callback) => {
                document.addEventListener("native-event", function (e) {
                    event = event.replace(/^(\\\\)+/, '');
                    e.detail.event = e.detail.event.replace(/^(\\\\)+/, '');

                    if (event === e.detail.event) {
                        return callback(e.detail.payload, event);
                    }
                });
            },
        };

        document.addEventListener("native-event", function (e) {
            e.detail.event = e.detail.event.replace(/^(\\\\)+/, '');

            if (window.Livewire) {
                window.Livewire.dispatch('native:' + e.detail.event, e.detail.payload);
            }
        });

        window.Native = Native;

        document.addEventListener("DOMContentLoaded", function() {
            // Disable zoom
            const meta = document.createElement('meta');
            meta.name = 'viewport';
            meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
            document.head.appendChild(meta);

            // Disable text selection
            document.body.style.userSelect = "none";
        });
        """
        let contentController = webView.configuration.userContentController
        let script = WKUserScript(
            source: helper,
            injectionTime: .atDocumentStart,
            forMainFrameOnly: true
        )
        contentController.addUserScript(script)
    }

    func addSwipeGestureSupport(context: Context) {
        webView.navigationDelegate = context.coordinator

        let swipeLeft = UISwipeGestureRecognizer(target: context.coordinator, action: #selector(Coordinator.handleSwipeLeft(_:)))
        swipeLeft.direction = .left
        webView.addGestureRecognizer(swipeLeft)

        let swipeRight = UISwipeGestureRecognizer(target: context.coordinator, action: #selector(Coordinator.handleSwipeRight(_:)))
        swipeRight.direction = .right
        webView.addGestureRecognizer(swipeRight)
    }

    func updateUIView(_ uiView: WKWebView, context: Context) {
        // Handle updates if needed
    }

    func load(url: URL) {
        webView.load(URLRequest(url: url))
    }
}

class ConsoleLogger: NSObject, WKScriptMessageHandler {
    func userContentController(_ userContentController: WKUserContentController, didReceive message: WKScriptMessage) {
        if let body = message.body as? [String: Any],
           let type = body["type"] as? String,
           let logMessage = body["message"] as? String {
            print()
            print("JS \(type): \(logMessage)")
        }
    }
}
