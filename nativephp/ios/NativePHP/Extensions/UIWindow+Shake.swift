//
//  UIWindow+Shake.swift
//  NativePHP
//
//  Created by Marcel Pociot on 31.03.25.
//
import UIKit

extension NSNotification.Name {
    public static let deviceDidShakeNotification = NSNotification.Name("DeviceDidShakeNotification")
    public static let reloadWebViewNotification = NSNotification.Name("ReloadWebViewNotification")
    public static let redirectToURLNotification = NSNotification.Name("RedirectToURLNotification")
}

extension UIWindow {
    open override func motionEnded(_ motion: UIEvent.EventSubtype, with event: UIEvent?) {
        super.motionEnded(motion, with: event)
        NotificationCenter.default.post(name: .deviceDidShakeNotification, object: event)
    }
}
