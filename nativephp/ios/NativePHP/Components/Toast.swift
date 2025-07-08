import UIKit

func showToast(message: String, duration: TimeInterval = 2.0) {
    guard let window = UIApplication.shared.connectedScenes
        .compactMap({ $0 as? UIWindowScene })
        .first?.windows
        .first(where: { $0.isKeyWindow }) else {
            return
        }

    let toastLabel = UILabel()
    toastLabel.backgroundColor = UIColor.black.withAlphaComponent(0.6)
    toastLabel.textColor = .white
    toastLabel.textAlignment = .center
    toastLabel.text = message
    toastLabel.alpha = 0.0
    toastLabel.layer.cornerRadius = 10
    toastLabel.clipsToBounds = true

    let maxWidthPercentage: CGFloat = 0.8
    let maxMessageSize = CGSize(width: window.frame.width * maxWidthPercentage, height: CGFloat.greatestFiniteMagnitude)
    let expectedSize = toastLabel.sizeThatFits(maxMessageSize)

    toastLabel.frame = CGRect(
        x: (window.frame.width - expectedSize.width - 20) / 2,
        y: window.frame.height - 100,
        width: expectedSize.width + 20,
        height: expectedSize.height + 10
    )

    window.addSubview(toastLabel)

    UIView.animate(withDuration: 0.5, animations: {
        toastLabel.alpha = 1.0
    }) { _ in
        UIView.animate(withDuration: 0.5, delay: duration, options: .curveEaseOut, animations: {
            toastLabel.alpha = 0.0
        }) { _ in
            toastLabel.removeFromSuperview()
        }
    }
}
