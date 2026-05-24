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
        $stmt = $conn->prepare("SELECT name, email, phone, address, city, zip, points, avatar_color, 
            dietary_preference, favorite_cuisine, spiciness_level,
            (SELECT COUNT(*) FROM orders WHERE user_email = users.email) as total_orders 
            FROM users WHERE email = ?");
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

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    try {
        // Dynamically compile columns to update based on request payload
        $fields = [];
        $params = [];
        
        $updatable_fields = [
            'name', 'phone', 'address', 'city', 'zip', 
            'avatar_color', 'dietary_preference', 'favorite_cuisine', 'spiciness_level'
        ];
        
        foreach ($updatable_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "`$field` = ?";
                $params[] = htmlspecialchars($data[$field]);
            }
        }
        
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update.']);
            exit;
        }
        
        $params[] = $email;
        $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE email = ?";
        
        $stmt = $conn->prepare($query);
        $success = $stmt->execute($params);

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
