<?php
/**
 * Order System Diagnostic - Remove this file after debugging!
 * Access at: http://localhost/zomato/order_debug.php
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<title>Order System Diagnostics</title>
<style>
    body { font-family: monospace; background: #0a0a0a; color: #eee; padding: 30px; }
    .ok  { color: #2ed573; font-weight: bold; }
    .err { color: #ff4757; font-weight: bold; }
    .warn { color: #ffa502; }
    pre  { background: #111; padding: 15px; border-radius: 8px; overflow-x: auto; border: 1px solid #222; }
    h2   { color: #d4af37; border-bottom: 1px solid #333; padding-bottom: 10px; }
</style>
</head>
<body>
<h1>🔧 Order System Diagnostics</h1>

<?php
require_once 'api/config.php';

// 1. DB Connection
echo "<h2>1. Database Connection</h2>";
if (isset($db_connection_failed) && $db_connection_failed) {
    echo "<p class='err'>❌ FAILED: " . htmlspecialchars($db_error_message) . "</p>";
} else {
    echo "<p class='ok'>✅ Connected to MySQL successfully</p>";
}

// 2. Check tables
echo "<h2>2. Table Check</h2>";
try {
    $tables = ['orders', 'order_items', 'users', 'foods'];
    foreach ($tables as $t) {
        $check = $conn->query("SHOW TABLES LIKE '$t'")->fetchColumn();
        if ($check) {
            $count = $conn->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "<p class='ok'>✅ Table <strong>$t</strong> exists — $count rows</p>";
        } else {
            echo "<p class='err'>❌ Table <strong>$t</strong> is MISSING</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='err'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 3. Check orders
echo "<h2>3. Recent Orders</h2>";
try {
    $orders = $conn->query("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    if (count($orders) === 0) {
        echo "<p class='warn'>⚠️ No orders found in database. Place a test order first.</p>";
    } else {
        echo "<pre>";
        foreach ($orders as $o) {
            echo "Order #{$o['id']} | {$o['user_email']} | ₹{$o['total_price']} | {$o['order_status']} | {$o['item_count']} items | {$o['created_at']}\n";
        }
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p class='err'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 4. Test place_order API directly
echo "<h2>4. Test Place Order API</h2>";
$testPayload = [
    'email'          => 'test@example.com',
    'cart'           => [
        ['name' => 'Test Burger', 'price' => 199, 'quantity' => 2],
        ['name' => 'Test Fries',  'price' => 99,  'quantity' => 1]
    ],
    'address'        => '123 Test Street, Test City',
    'payment_method' => 'cod',
    'total_price'    => 497
];

echo "<p>Sending test order for <code>test@example.com</code>...</p>";

try {
    $conn->beginTransaction();
    $stmt = $conn->prepare("INSERT INTO orders (user_email, total_price, delivery_address, payment_method, order_status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$testPayload['email'], $testPayload['total_price'], $testPayload['address'], $testPayload['payment_method']]);
    $order_id = $conn->lastInsertId();

    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, food_name, price, quantity) VALUES (?, ?, ?, ?)");
    foreach ($testPayload['cart'] as $item) {
        $stmtItem->execute([$order_id, $item['name'], $item['price'], $item['quantity']]);
    }
    $conn->rollBack(); // Don't actually save the test order

    echo "<p class='ok'>✅ Test order would succeed! Order ID would be: <strong>$order_id</strong> (rolled back — not saved)</p>";
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo "<p class='err'>❌ Test FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 5. PHP Info
echo "<h2>5. PHP Environment</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "Max POST size: " . ini_get('post_max_size') . "\n";
echo "</pre>";

echo "<hr style='border-color:#333; margin: 30px 0;'>";
echo "<p class='warn'>⚠️ Delete <code>order_debug.php</code> after debugging!</p>";
?>
</body>
</html>
