<?php
// Ensure this script only returns JSON
header('Content-Type: application/json');

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Extract fields safely
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
$name = isset($data['name']) ? htmlspecialchars(trim($data['name'])) : 'Foodie';
$otp = isset($data['otp']) ? trim($data['otp']) : '';

// Basic validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address provided.']);
    exit;
}

if (empty($otp) || strlen($otp) !== 4) {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP generated.']);
    exit;
}

// Prepare the email
$subject = "Your FoodieHub OTP Code";

// HTML Email Body
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
    
    <p>This code will expire shortly. If you did not request this code, please safely ignore this email.</p>
    
    <div class='footer'>
      &copy; " . date('Y') . " FoodieHub. All rights reserved.<br>
      Taste the world, one dash at a time.
    </div>
  </div>
</body>
</html>
";

// To send HTML mail, the Content-type header must be set
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

// Additional headers
$headers .= "From: FoodieHub <noreply@foodiehub.local>\r\n";
$headers .= "Reply-To: noreply@foodiehub.local\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Attempt to send the email
// IMPORTANT: XAMPP on Windows must have sendmail configured for this to work natively.
$mailSent = @mail($email, $subject, $message, $headers);

if ($mailSent) {
    echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
} else {
    // If mail fails, provide a fallback message (often due to unconfigured SMTP in XAMPP)
    // For development/testing purposes, if the user hasn't configured XAMPP, we'll return an error.
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email. Ensure XAMPP SMTP/sendmail is configured correctly in php.ini.'
    ]);
}
?>
