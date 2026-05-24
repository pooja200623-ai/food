<?php
$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'custom_app_db';

try {
    $conn = new PDO("mysql:host=$host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create DB if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name");
    $conn->exec("USE $db_name");

    echo "Ensuring database schema is up to date...<br>";

    // 1. Ensure 'orders' table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(255) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        order_status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Check for missing columns in 'orders'
    $cols = $conn->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('delivery_address', $cols)) {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_address TEXT AFTER total_price");
        echo "Added 'delivery_address' column.<br>";
    }
    
    if (!in_array('payment_method', $cols)) {
        $conn->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) AFTER delivery_address");
        echo "Added 'payment_method' column.<br>";
    }

    // 3. Ensure 'order_items' table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        food_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");

    // 4. Ensure 'users' table columns exist
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        otp VARCHAR(10),
        otp_expiry DATETIME
    )");

    $userCols = $conn->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $missingUserCols = [
        'phone' => "VARCHAR(20) AFTER email",
        'address' => "TEXT AFTER phone",
        'city' => "VARCHAR(100) AFTER address",
        'zip' => "VARCHAR(20) AFTER city",
        'points' => "INT DEFAULT 0 AFTER zip",
        'avatar_color' => "VARCHAR(20) DEFAULT '#d4af37' AFTER points",
        'dietary_preference' => "VARCHAR(50) DEFAULT 'None' AFTER avatar_color",
        'favorite_cuisine' => "VARCHAR(50) DEFAULT 'None' AFTER dietary_preference",
        'spiciness_level' => "VARCHAR(50) DEFAULT 'Medium' AFTER favorite_cuisine"
    ];

    foreach ($missingUserCols as $col => $definition) {
        if (!in_array($col, $userCols)) {
            $conn->exec("ALTER TABLE users ADD COLUMN $col $definition");
            echo "Added '$col' column to users table.<br>";
        }
    }

    echo "Database structure is verified and fixed! You can now place orders.";

} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
