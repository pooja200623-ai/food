<?php
header('Content-Type: application/json');

$host = 'localhost';
$db_name = 'custom_app_db';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if ($action === 'place_order') {
    $email = isset($data['email']) ? $data['email'] : '';
    $cart = isset($data['cart']) ? $data['cart'] : [];
    $total_price = isset($data['total_price']) ? $data['total_price'] : 0;

    if (empty($email) || empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_email, total_price, order_status) VALUES (?, ?, 'Pending')");
        $stmt->execute([$email, $total_price]);
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
