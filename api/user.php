<?php
header('Content-Type: application/json');
require_once 'config.php';

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_profile') {
    $email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT name, email, phone, address, city, zip, points, avatar_color FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'update_profile') {
    $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : '';
    $name = isset($data['name']) ? htmlspecialchars($data['name']) : '';
    $phone = isset($data['phone']) ? htmlspecialchars($data['phone']) : '';
    $address = isset($data['address']) ? htmlspecialchars($data['address']) : '';
    $city = isset($data['city']) ? htmlspecialchars($data['city']) : '';
    $zip = isset($data['zip']) ? htmlspecialchars($data['zip']) : '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ?, zip = ? WHERE email = ?");
        $success = $stmt->execute([$name, $phone, $address, $city, $zip, $email]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
