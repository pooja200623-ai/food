<?php
header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// -------------------------------------------------------
// Try to connect to DB. If it fails, use session-based
// fallback so the app still works for demo/offline use.
// -------------------------------------------------------
$dbAvailable = false;
$conn = null;

$host = 'localhost';
$db_name = 'foodiehub_db';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbAvailable = true;
} catch(PDOException $e) {
    // DB not available - will use session fallback
    $dbAvailable = false;
}

// Start session for OTP fallback
session_start();

// -------------------------------------------------------
// ACTION: send_otp
// -------------------------------------------------------
if ($action === 'send_otp') {
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $name  = isset($data['name'])  ? htmlspecialchars(trim($data['name'])) : 'Foodie';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    // Generate OTP on the SERVER (never sent from client)
    $otp    = strval(rand(1000, 9999));
    $expiry = time() + 600; // 10 minutes

    // Store in DB if available
    if ($dbAvailable) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $expiryDt = date('Y-m-d H:i:s', $expiry);
            if ($user) {
                $conn->prepare("UPDATE users SET name=?, otp=?, otp_expiry=? WHERE email=?")->execute([$name, $otp, $expiryDt, $email]);
            } else {
                $conn->prepare("INSERT INTO users (name, email, otp, otp_expiry) VALUES (?, ?, ?, ?)")->execute([$name, $email, $otp, $expiryDt]);
            }
        } catch(PDOException $e) {
            // Fall through to session fallback
        }
    }

    // Always store in session as fallback
    $_SESSION['otp_' . md5($email)] = ['otp' => $otp, 'expiry' => $expiry, 'name' => $name, 'email' => $email];

    // Try to send email
    $subject = "Your Zomato Login OTP";
    $body = "
    <html><body style='font-family:Arial,sans-serif;'>
    <div style='max-width:500px;margin:0 auto;padding:30px;border-radius:12px;border:1px solid #eee;'>
      <h2 style='color:#E23744;'>Zomato</h2>
      <p>Hi <strong>{$name}</strong>,</p>
      <p>Your One-Time Password (OTP) for login is:</p>
      <div style='font-size:36px;font-weight:bold;color:#E23744;letter-spacing:8px;text-align:center;margin:24px 0;'>{$otp}</div>
      <p style='color:#666;font-size:0.9em;'>This code expires in 10 minutes. Do not share it with anyone.</p>
      <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
      <p style='color:#999;font-size:0.8em;'>&copy; " . date('Y') . " Zomato. All rights reserved.</p>
    </div></body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Zomato <noreply@zomato.local>\r\n";

    $mailSent = @mail($email, $subject, $body, $headers);

    // Always return success but include dev_otp if mail failed (for local dev)
    $response = ['success' => true, 'message' => 'OTP sent to ' . $email];
    if (!$mailSent) {
        $response['dev_otp'] = $otp; // Show OTP in toast for local development
        $response['message'] = 'Email not configured. Dev mode: OTP shown below.';
    }

    echo json_encode($response);
    exit;
}

// -------------------------------------------------------
// ACTION: verify_otp
// -------------------------------------------------------
if ($action === 'verify_otp') {
    $email      = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $enteredOTP = isset($data['otp'])   ? trim($data['otp']) : '';

    if (empty($email) || empty($enteredOTP)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
        exit;
    }

    $sessionKey = 'otp_' . md5($email);
    $verified   = false;
    $userData   = null;

    // Try DB first
    if ($dbAvailable) {
        try {
            $stmt = $conn->prepare("SELECT id, name, email, otp, otp_expiry FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && $user['otp'] === $enteredOTP) {
                $now    = new DateTime();
                $expiry = new DateTime($user['otp_expiry']);
                if ($now <= $expiry) {
                    // Clear OTP after use
                    $conn->prepare("UPDATE users SET otp=NULL, otp_expiry=NULL WHERE id=?")->execute([$user['id']]);
                    $verified = true;
                    $userData = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
                } else {
                    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
                    exit;
                }
            }
        } catch(PDOException $e) {
            // Fall through to session check
        }
    }

    // Session fallback if DB not available or user not found in DB
    if (!$verified && isset($_SESSION[$sessionKey])) {
        $sess = $_SESSION[$sessionKey];
        if ($sess['otp'] === $enteredOTP) {
            if (time() <= $sess['expiry']) {
                $verified = true;
                $userData = ['id' => null, 'name' => $sess['name'], 'email' => $sess['email']];
                unset($_SESSION[$sessionKey]);
            } else {
                echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
                exit;
            }
        }
    }

    if ($verified && $userData) {
        echo json_encode(['success' => true, 'message' => 'Login successful!', 'user' => $userData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please check and try again.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
