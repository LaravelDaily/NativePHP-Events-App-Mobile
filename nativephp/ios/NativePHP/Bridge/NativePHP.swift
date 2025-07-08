import UIKit
import AudioToolbox
import LocalAuthentication
import AVFoundation
import Security

final class LaravelBridge {
    static let shared = LaravelBridge()

    var send: ((_ event: String, _ payload: [String: Any]) -> Void)?
}

// MARK: - Share sheet

@_cdecl("NativePHPShowShareSheet")
public func NativePHPShowShareSheet(
    cTitle: UnsafePointer<CChar>?,
    cText:  UnsafePointer<CChar>?,
    cUrl:   UnsafePointer<CChar>?
) {
    // Convert C strings to Swift strings
    guard
        let cTitle = cTitle, let cText = cText, let cUrl = cUrl
    else {
        return
    }
    
    let title = String(cString: cTitle)
    let text  = String(cString: cText)
    let url   = String(cString: cUrl)
    
    // Construct the items you want to share
    // e.g., you might combine them or interpret them as needed.
    var shareItems: [Any] = []
    if !title.isEmpty { shareItems.append(title) }
    if !text.isEmpty  { shareItems.append(text) }
    if !url.isEmpty   { shareItems.append(URL(string: url) ?? url) }
    
    DispatchQueue.main.async {
        // Get the topmost view controller from the active scene
        guard let window = UIApplication.shared.connectedScenes
            .filter({ $0.activationState == .foregroundActive })
            .map({ $0 as? UIWindowScene })
            .compactMap({ $0 })
            .first?.windows
            .filter({ $0.isKeyWindow }).first else {
            return
        }
        guard let rootVC = window.rootViewController else {
            return
        }
        
        // Create and present a UIActivityViewController
        let activityVC = UIActivityViewController(
            activityItems: shareItems,
            applicationActivities: nil
        )
        
        // On iPad, UIActivityViewController must be presented in a popover.
        if let popover = activityVC.popoverPresentationController {
            popover.sourceView = rootVC.view // or another view that makes sense in your context
            popover.permittedArrowDirections = .any
        }
        
        rootVC.present(activityVC, animated: true, completion: nil)
    }
}

// MARK: - Alert

@_silgen_name("NativePHPAlertReset")
func NativePHPAlertReset()

@_cdecl("NativePHPShowAlert")
public func NativePHPShowAlert(_ cTitle: UnsafePointer<CChar>?,
                               _ cMessage: UnsafePointer<CChar>?,
                               _ cButtonTitles: UnsafePointer<UnsafePointer<CChar>?>?,
                               _ cButtonCount: Int32) {

    DispatchQueue.main.async {

        guard let scene = UIApplication.shared.connectedScenes
                  .compactMap({ $0 as? UIWindowScene })
                  .first(where: { $0.activationState == .foregroundActive }),
              let root = scene.windows.first(where: { $0.isKeyWindow })?.rootViewController else {
            NativePHPAlertReset()
            return
        }

        let title   = cTitle.map { String(cString: $0) } ?? ""
        let message = cMessage.map { String(cString: $0) } ?? ""

        var buttons: [String] = []
        if let base = cButtonTitles {
            for i in 0..<Int(cButtonCount) {
                if let ptr = base[i] {
                    buttons.append(String(cString: ptr))
                }
            }
        }
        
        if buttons.isEmpty {
            buttons = ["OK"]
        }

        // Build and present the UIAlertController
        let alert = UIAlertController(title: title,
                                      message: message,
                                      preferredStyle: .alert)

        for (index, label) in buttons.enumerated() {
            alert.addAction(UIAlertAction(title: label,
                                          style: .default) { _ in
                // JS / Laravel event
                LaravelBridge.shared.send?(
                    "Native\\Mobile\\Events\\Alert\\ButtonPressed",
                    ["index": index, "title": label]
                )

                NativePHPAlertReset()
            })
        }

        root.present(alert, animated: true)
    }
}

// MARK: - Toast

@_cdecl("NativePHPShowToast")
func NativePHPShowToast(_ message: UnsafePointer<CChar>) {
    let msg = String(cString: message)
    DispatchQueue.main.async {
        showToast(message: msg)
    }
}

// MARK: - Flashlight

@_cdecl("NativePHPToggleFlashlight")
func NativePHPToggleFlashlight() {
    guard let device = AVCaptureDevice.default(for: .video), device.hasTorch else {
        return
    }

    do {
        try device.lockForConfiguration()
        device.torchMode = device.torchMode == .on ? .off : .on
        device.unlockForConfiguration()
    } catch {
        print("Failed to toggle flashlight: \(error)")
    }
}

// MARK: - Vibrate

@_cdecl("NativePHPVibrate")
public func NativePHPVibrate() {
    // For example, trigger haptic feedback using UIKit or CoreHaptics
    // Example: simple "vibrate" using AudioServicesPlaySystemSound
    AudioServicesPlaySystemSound(kSystemSoundID_Vibrate)
}

// MARK: - Camera

@_silgen_name("NativePHPCameraReset")
func NativePHPCameraReset()

@_cdecl("NativePHPOpenCamera")
public func NativePHPOpenCamera() {
    // Always hop to the main queue so UI work is on the main thread.
    DispatchQueue.main.async {
        // 1. Find the active foreground window-scene.
        guard let windowScene = UIApplication.shared.connectedScenes
                .compactMap({ $0 as? UIWindowScene })
                .first(where: { $0.activationState == .foregroundActive }),

              // 2. Grab that scene’s *key* window.
              let rootVC = windowScene.windows
                .first(where: { $0.isKeyWindow })?
                .rootViewController else {

            NativePHPCameraReset()
            return
        }

        let picker = UIImagePickerController()
        picker.sourceType = .camera
        picker.delegate   = MyCameraDelegate.shared
        rootVC.present(picker, animated: true)
    }
}

final class MyCameraDelegate: NSObject,
                              UIImagePickerControllerDelegate,
                              UINavigationControllerDelegate {

    static let shared = MyCameraDelegate()

    // User captured a photo
    func imagePickerController(_ picker: UIImagePickerController,
                               didFinishPickingMediaWithInfo info: [UIImagePickerController.InfoKey: Any]) {

        picker.dismiss(animated: true)

        // 1. Get full-quality JPEG data
        guard let image = info[.originalImage] as? UIImage,
              let jpegData = image.jpegData(compressionQuality: 1.0) else {
            NativePHPCameraReset()
            return
        }

        // 2. Save on a background queue
        DispatchQueue.global(qos: .utility).async {
            let fm = FileManager.default

            // 2a. ~/Library/Application Support/Photos/
            guard let appSupport = fm.urls(for: .applicationSupportDirectory, in: .userDomainMask).first else {
                NativePHPCameraReset()
                return
            }
            let photosDir = appSupport.appendingPathComponent("Photos", isDirectory: true)
            try? fm.createDirectory(at: photosDir, withIntermediateDirectories: true)

            // 2b. Fixed file name
            var fileURL = photosDir.appendingPathComponent("captured.jpg")

            do {
                // 3. Atomic overwrite
                try jpegData.write(to: fileURL, options: .atomic)

                // 4. Exclude from iCloud / iTunes backup
                var resourceValues = URLResourceValues()
                resourceValues.isExcludedFromBackup = true
                try fileURL.setResourceValues(resourceValues)

                LaravelBridge.shared.send?(
                    "Native\\Mobile\\Events\\Camera\\PhotoTaken",
                    ["path": fileURL.path(percentEncoded: false)]
                )

            } catch {
                print("Saving image failed: \(error)")
            }

            // 6. Release the PHP lock
            NativePHPCameraReset()
        }
    }

    // User hit “Cancel”
    func imagePickerControllerDidCancel(_ picker: UIImagePickerController) {
        picker.dismiss(animated: true)

        // (No image to send.)  Just unlock PHP.
        NativePHPCameraReset()
    }
}

// MARK: - Biometrics

@_silgen_name("NativePHPLocalAuthReset")
func NativePHPLocalAuthReset()

@_cdecl("NativePHPLocalAuthChallenge")
public func NativePHPLocalAuthChallenge() {
    DispatchQueue.main.async {
        let context = LAContext()
        var error: NSError?

        // Check availability
        guard context.canEvaluatePolicy(.deviceOwnerAuthentication, error: &error) else {
            LaravelBridge.shared.send?(
                "Native\\Mobile\\Events\\Biometric\\Completed",
                ["success": false]
            )
            
            NativePHPLocalAuthReset()
            
            return
        }

        // Present Face/Touch ID sheet
        context.evaluatePolicy(.deviceOwnerAuthentication,
                           localizedReason: "Authenticate") { success, error in
            DispatchQueue.main.async {
                if success {
                    LaravelBridge.shared.send?(
                        "Native\\Mobile\\Events\\Biometric\\Completed",
                        ["success": true]
                    )
                } else {
                    LaravelBridge.shared.send?(
                        "Native\\Mobile\\Events\\Biometric\\Completed",
                        ["success": false]
                    )
                }

                NativePHPLocalAuthReset()
            }
        }
    }
}

// MARK: - Push Notifications

@_cdecl("NativePHPRegisterForPushNotifications")
public func NativePHPRegisterForPushNotifications() {
    UNUserNotificationCenter.current().requestAuthorization(options: [.alert, .badge, .sound]) { granted, error in
        guard error == nil else {
            print("Error requesting notification permission:", error!)
            return
        }
        
        guard granted else {
            print("User denied push notification permission.")
            return
        }

        DispatchQueue.main.async {
            UIApplication.shared.registerForRemoteNotifications()
        }
    }
}

@_silgen_name("NativePHPSetPushToken")
func NativePHPSetPushTokenC(_ token: UnsafePointer<CChar>?)

// MARK: - Secure Storage

@_cdecl("NativePHPSecureGet")
public func NativePHPSecureGet(_ cKey: UnsafePointer<CChar>?) -> UnsafePointer<CChar>? {
    guard let cKey = cKey else { return nil }
    
    let key = String(cString: cKey)
    let service = Bundle.main.bundleIdentifier ?? "com.nativephp.app"
    
    let query: [String: Any] = [
        kSecClass as String: kSecClassGenericPassword,
        kSecAttrAccount as String: key,
        kSecAttrService as String: service,
        kSecReturnData as String: true,
        kSecMatchLimit as String: kSecMatchLimitOne
    ]
    
    var result: AnyObject?
    let status = SecItemCopyMatching(query as CFDictionary, &result)
    
    if status == errSecSuccess, 
       let data = result as? Data, 
       let value = String(data: data, encoding: .utf8) {
        return UnsafePointer(strdup(value))
    }
    
    return nil
}

@_cdecl("NativePHPSecureSet")
public func NativePHPSecureSet(_ cKey: UnsafePointer<CChar>?, _ cValue: UnsafePointer<CChar>?) {
    guard let cKey = cKey else { return }
    
    let key = String(cString: cKey)
    let service = Bundle.main.bundleIdentifier ?? "com.nativephp.app"
    
    let query: [String: Any] = [
        kSecClass as String: kSecClassGenericPassword,
        kSecAttrAccount as String: key,
        kSecAttrService as String: service
    ]
    
    // If cValue is null or empty string, delete the item
    if cValue == nil {
        SecItemDelete(query as CFDictionary)
        return
    }
    
    let value = String(cString: cValue!)
    if value.isEmpty {
        SecItemDelete(query as CFDictionary)
        return
    }
    
    guard let valueData = value.data(using: .utf8) else { return }
    
    // Check if item already exists
    let status = SecItemCopyMatching(query as CFDictionary, nil)
    
    if status == errSecSuccess {
        // Update existing item
        let updateQuery: [String: Any] = [
            kSecValueData as String: valueData
        ]
        SecItemUpdate(query as CFDictionary, updateQuery as CFDictionary)
    } else if status == errSecItemNotFound {
        // Add new item
        let addQuery: [String: Any] = [
            kSecClass as String: kSecClassGenericPassword,
            kSecAttrAccount as String: key,
            kSecAttrService as String: service,
            kSecValueData as String: valueData,
            kSecAttrAccessible as String: kSecAttrAccessibleWhenUnlockedThisDeviceOnly
        ]
        SecItemAdd(addQuery as CFDictionary, nil)
    }
}
