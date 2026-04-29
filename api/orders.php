<?php
require_once 'db.php';
header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'place_order') {
    // Expecting: user_id, items (array of {id, quantity, price})
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $items = isset($data['items']) ? $data['items'] : [];
    
    if ($user_id === 0 || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Invalid order data']);
        exit;
    }
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += ($item['price'] * $item['quantity']);
    }
    
    try {
        $conn->beginTransaction();
        
        // Insert into orders
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Processing')");
        $stmt->execute([$user_id, $total_amount]);
        $order_id = $conn->lastInsertId();
        
        // Insert into order_items
        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $itemStmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $order_id]);
        
    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'history') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id === 0) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $orders]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
