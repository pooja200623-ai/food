<?php
require_once 'db.php';
header('Content-Type: application/json');

// Get raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'send_otp') {
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $name = isset($data['name']) ? htmlspecialchars(trim($data['name'])) : 'Foodie';
    $otp = isset($data['otp']) ? trim($data['otp']) : '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address provided.']);
        exit;
    }

    if (empty($otp) || strlen($otp) !== 4) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP generated.']);
        exit;
    }

    // Save or update user in database
    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Expiry time (e.g., 10 minutes from now)
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        if ($user) {
            // Update existing user with new OTP
            $updateStmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
            $updateStmt->execute([$otp, $expiry, $email]);
        } else {
            // Create new user
            $insertStmt = $conn->prepare("INSERT INTO users (name, email, otp, otp_expiry) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$name, $email, $otp, $expiry]);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Prepare the email
    $subject = "Your FoodieHub OTP Code";
    $message = "
    <html>
    <head>
      <title>FoodieHub OTP</title>
      <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; background-color: #fafafa; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { color: #FF5F52; }
        .otp-code { font-size: 32px; font-weight: bold; color: #FF5F52; text-align: center; margin: 20px 0; letter-spacing: 5px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 30px; }
      </style>
    </head>
    <body>
      <div class='container'>
        <div class='header'>
          <h2>Welcome to FoodieHub!</h2>
        </div>
        <p>Hello <strong>{$name}</strong>,</p>
        <p>We received a request to log in to your FoodieHub account. Please use the following One-Time Password (OTP) to complete your verification:</p>
        
        <div class='otp-code'>{$otp}</div>
        
        <p>This code will expire in 10 minutes. If you did not request this code, please safely ignore this email.</p>
        
        <div class='footer'>
          &copy; " . date('Y') . " FoodieHub. All rights reserved.<br>
          Taste the world, one dash at a time.
        </div>
      </div>
    </body>
    </html>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: FoodieHub <noreply@foodiehub.local>\r\n";
    $headers .= "Reply-To: noreply@foodiehub.local\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $mailSent = @mail($email, $subject, $message, $headers);

    if ($mailSent) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
    } else {
        // Fallback for development if XAMPP SMTP is not configured
        echo json_encode([
            'success' => true, // Return true so dev doesn't block
            'message' => 'Failed to send real email (XAMPP SMTP not configured). Dev mode active.',
            'dev_otp' => $otp
        ]);
    }

} elseif ($action === 'verify_otp') {
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $enteredOTP = isset($data['otp']) ? trim($data['otp']) : '';

    if (empty($email) || empty($enteredOTP)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, name, email, otp, otp_expiry FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $now = new DateTime();
            $expiry = new DateTime($user['otp_expiry']);

            if ($user['otp'] === $enteredOTP) {
                if ($now <= $expiry) {
                    // OTP is valid and not expired
                    // Clear the OTP to prevent reuse
                    $updateStmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE id = ?");
                    $updateStmt->execute([$user['id']]);

                    echo json_encode([
                        'success' => true, 
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email']
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'OTP has expired.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>
