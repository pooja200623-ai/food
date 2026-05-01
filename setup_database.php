<?php
$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'custom_app_db';

try {
    // Connect without DB name first to create it
    $conn = new PDO("mysql:host=$host;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    echo "Database created successfully.<br>";
    
    // Connect to the new database
    $conn->exec("USE `$db_name`");
    
    // Create Foods table
    $conn->exec("DROP TABLE IF EXISTS foods");
    $conn->exec("CREATE TABLE foods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        rating DECIMAL(3,1) DEFAULT 4.5,
        image_url VARCHAR(500) NOT NULL,
        delivery_time VARCHAR(50) DEFAULT '30 min',
        description TEXT
    )");
    echo "Foods table created.<br>";
    
    // Create Orders table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(255) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        delivery_address TEXT,
        payment_method VARCHAR(50),
        order_status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Orders table created.<br>";

    // Create Order Items table
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        food_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT DEFAULT 1,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");
    echo "Order items table created.<br>";
    
    // Check if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM foods");
    if ($stmt->fetchColumn() == 0) {
        require_once 'food_data.php';
        $foods = $global_foods;
        
        $insert = $conn->prepare("INSERT INTO foods (name, category, price, rating, image_url, delivery_time, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($foods as $f) {
            $insert->execute($f);
        }
        echo "Seeded ".count($foods)." foods.<br>";
    } else {
        echo "Foods table already seeded.<br>";
    }
    
    echo "<h2>Setup Complete! You can now close this page.</h2>";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
