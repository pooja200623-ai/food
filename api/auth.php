<?php
header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Start session for OTP fallback since we might not have the DB set up yet
session_start();

require_once 'config.php';
require_once 'mail_helper.php';
$dbAvailable = true; // Set to true as config.php handles failure via exit

// -------------------------------------------------------
// ACTION: send_otp
// -------------------------------------------------------
if ($action === 'send_otp') {
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $name  = isset($data['name'])  ? htmlspecialchars(trim($data['name'])) : '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required.']);
        exit;
    }

    // Generate 4-digit OTP
    $otp = strval(rand(1000, 9999));
    $expiry = time() + 600; // 10 minutes

    // Store in DB if available
    if ($dbAvailable) {
        try {
            // Create table if not exists (quick setup)
            $conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(100) UNIQUE,
                phone VARCHAR(20),
                address TEXT,
                city VARCHAR(100),
                zip VARCHAR(20),
                points INT DEFAULT 0,
                avatar_color VARCHAR(20) DEFAULT '#ff4757',
                otp VARCHAR(10),
                otp_expiry DATETIME
            )");

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
            // DB error, fallback to session
        }
    }

    // Always store in session as primary/fallback mechanism
    $_SESSION['otp_' . md5($email)] = ['otp' => $otp, 'expiry' => $expiry, 'name' => $name, 'email' => $email];

    // Send the actual email
    $mailResult = sendOTPEmail($email, $name, $otp);

    if ($mailResult['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'OTP has been sent to your email.'
        ]);
    } else {
        // Log error internally if possible, but return failure to user
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send OTP email. ' . $mailResult['message'],
            'dev_otp' => $otp // Still provide dev_otp on failure so user can proceed during setup
        ]);
    }
    exit;
}

// -------------------------------------------------------
// ACTION: verify_otp
// -------------------------------------------------------
if ($action === 'verify_otp') {
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $enteredOTP = isset($data['otp']) ? trim($data['otp']) : '';

    if (empty($email) || empty($enteredOTP)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
        exit;
    }

    $sessionKey = 'otp_' . md5($email);
    $verified = false;
    $userData = null;

    // Try Session first (it's always set)
    if (isset($_SESSION[$sessionKey])) {
        $sess = $_SESSION[$sessionKey];
        if ($sess['otp'] === $enteredOTP) {
            if (time() <= $sess['expiry']) {
                $verified = true;
                $userData = ['name' => $sess['name'], 'email' => $sess['email']];
                unset($_SESSION[$sessionKey]); // Clear OTP
            } else {
                echo json_encode(['success' => false, 'message' => 'OTP has expired.']);
                exit;
            }
        }
    }

    // Fallback to DB if session didn't work but DB is available
    if (!$verified && $dbAvailable) {
        try {
            $stmt = $conn->prepare("SELECT id, name, email, otp, otp_expiry FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && $user['otp'] === $enteredOTP) {
                $now = new DateTime();
                $expiry = new DateTime($user['otp_expiry']);
                if ($now <= $expiry) {
                    $conn->prepare("UPDATE users SET otp=NULL, otp_expiry=NULL WHERE id=?")->execute([$user['id']]);
                    $verified = true;
                    $userData = ['name' => $user['name'], 'email' => $user['email']];
                } else {
                    echo json_encode(['success' => false, 'message' => 'OTP has expired.']);
                    exit;
                }
            }
        } catch(PDOException $e) {
            // DB Error
        }
    }

    if ($verified && $userData) {
        echo json_encode(['success' => true, 'message' => 'Login successful!', 'user' => $userData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
