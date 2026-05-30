<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

// Check if DB connection failed
if (isset($db_connection_failed) && $db_connection_failed) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $db_error_message]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($action === 'place_order') {
    $email         = isset($data['email'])          ? trim($data['email'])          : '';
    $cart          = isset($data['cart'])            ? $data['cart']                : [];
    $total_price   = isset($data['total_price'])     ? floatval($data['total_price']) : 0;
    $address       = isset($data['address'])         ? trim($data['address'])       : '';
    $payment_method = isset($data['payment_method']) ? trim($data['payment_method']) : 'cod';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'User email is required.']);
        exit;
    }

    if (empty($cart) || !is_array($cart)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
        exit;
    }

    if (empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Delivery address is required.']);
        exit;
    }

    if ($total_price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid total price.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Insert into orders table
        $stmt = $conn->prepare(
            "INSERT INTO orders (user_email, total_price, delivery_address, payment_method, order_status) 
             VALUES (?, ?, ?, ?, 'Pending')"
        );
        $stmt->execute([$email, $total_price, $address, $payment_method]);
        $order_id = $conn->lastInsertId();

        if (!$order_id) {
            throw new Exception("Failed to create order record.");
        }

        // 2. Insert each cart item into order_items table
        $stmtItem = $conn->prepare(
            "INSERT INTO order_items (order_id, food_name, price, quantity) VALUES (?, ?, ?, ?)"
        );

        foreach ($cart as $item) {
            $food_name = isset($item['name'])     ? trim($item['name'])      : '';
            $price     = isset($item['price'])    ? floatval($item['price']) : 0;
            $quantity  = isset($item['quantity']) ? intval($item['quantity']) : 1;

            if (empty($food_name)) continue;

            $stmtItem->execute([$order_id, $food_name, $price, $quantity]);
        }

        $conn->commit();

        echo json_encode([
            'success'  => true,
            'message'  => 'Order placed successfully!',
            'order_id' => $order_id
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to place order: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($action === 'get_orders') {
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "SELECT * FROM orders WHERE user_email = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$email]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $stmtItems = $conn->prepare(
                "SELECT * FROM order_items WHERE order_id = ?"
            );
            $stmtItems->execute([$order['id']]);
            $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($order);

        echo json_encode(['success' => true, 'data' => $orders]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch orders: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_order') {
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Order ID required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit;
        }

        $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$order_id]);
        $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $order]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch order: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
