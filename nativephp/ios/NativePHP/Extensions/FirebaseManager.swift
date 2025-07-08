import Foundation
import FirebaseCore

class FirebaseManager {
    static let shared = FirebaseManager()
    private(set) var isConfigured = false

    func configureIfAvailable() {
        guard Bundle.main.path(forResource: "GoogleService-Info", ofType: "plist") != nil else {
            print("⚠️ Skipping Firebase configuration — GoogleService-Info.plist not found.")
            return
        }

        FirebaseApp.configure()
        isConfigured = true
        
        print("✅ Firebase configured.")
    }
}
