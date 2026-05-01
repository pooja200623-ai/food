<?php
header('Content-Type: application/json');

require_once 'config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($action === 'place_order') {
    $email = isset($data['email']) ? $data['email'] : '';
    $cart = isset($data['cart']) ? $data['cart'] : [];
    $total_price = isset($data['total_price']) ? $data['total_price'] : 0;
    $address = isset($data['address']) ? $data['address'] : '';
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cod';

    if (empty($email) || empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_email, total_price, delivery_address, payment_method, order_status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->execute([$email, $total_price, $address, $payment_method]);
        $order_id = $conn->lastInsertId();

        // 2. Insert items into order_items table
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, food_name, price, quantity) VALUES (?, ?, ?, ?)");
        foreach ($cart as $item) {
            $stmtItem->execute([$order_id, $item['name'], $item['price'], $item['quantity']]);
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!', 'order_id' => $order_id]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_orders') {
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_email = ? ORDER BY created_at DESC");
        $stmt->execute([$email]);
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$order['id']]);
            $order['items'] = $stmtItems->fetchAll();
        }

        echo json_encode(['success' => true, 'data' => $orders]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch orders.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
