<?php
header('Content-Type: application/json');

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ----------------------------------------------------------
// Try DB connection
// ----------------------------------------------------------
$conn = null;
$dbAvailable = false;
try {
    $conn = new PDO("mysql:host=localhost;dbname=foodiehub_db;charset=utf8", 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbAvailable = true;
} catch(PDOException $e) {
    $dbAvailable = false;
}

// ----------------------------------------------------------
// ACTION: place_order
// ----------------------------------------------------------
if ($action === 'place_order') {
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 1;
    $items   = isset($data['items']) ? $data['items'] : [];

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No items in order.']);
        exit;
    }

    $total = 0;
    foreach ($items as $item) {
        $total += ($item['price'] * $item['quantity']);
    }
    $total += 50; // taxes

    if ($dbAvailable) {
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Processing')");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $itemStmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            }
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Order placed!', 'order_id' => $order_id]);
        } catch(PDOException $e) {
            $conn->rollBack();
            // Fallback - still show success for demo
            $order_id = 'ZO-' . rand(10000, 99999);
            echo json_encode(['success' => true, 'message' => 'Order placed (demo mode)!', 'order_id' => $order_id]);
        }
    } else {
        // Offline demo mode
        $order_id = 'ZO-' . rand(10000, 99999);
        echo json_encode(['success' => true, 'message' => 'Order placed (demo mode)!', 'order_id' => $order_id]);
    }
    exit;
}

// ----------------------------------------------------------
// ACTION: history
// ----------------------------------------------------------
if ($action === 'history') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($user_id === 0) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    if ($dbAvailable) {
        try {
            $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $orders = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $orders]);
        } catch(PDOException $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
    } else {
        echo json_encode(['success' => true, 'data' => []]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
