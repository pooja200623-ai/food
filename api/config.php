<?php
// Database configuration
$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'custom_app_db';
$port = '3306'; // Default MySQL port

// SMTP Configuration (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'pooja200623@gmail.com');
define('SMTP_PASS', 'your-app-password'); 
define('SMTP_FROM_NAME', 'Crave Food App');

try {
    // Attempt to connect to MySQL (without db name first to ensure we can create it if missing)
    $conn = new PDO("mysql:host=$host;port=$port;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Auto-create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $conn->exec("USE `$db_name` ");
    
    // Check if tables exist, if not, basic setup
    $conn->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_email` varchar(255) NOT NULL,
        `total_price` decimal(10,2) NOT NULL,
        `delivery_address` text,
        `payment_method` varchar(50) DEFAULT 'cod',
        `order_status` varchar(50) DEFAULT 'Pending',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS `order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `food_name` varchar(255) NOT NULL,
        `price` decimal(10,2) NOT NULL,
        `quantity` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100),
        `email` varchar(100) UNIQUE,
        `phone` varchar(20),
        `address` TEXT,
        `city` varchar(100),
        `zip` varchar(20),
        `points` int DEFAULT 0,
        `avatar_color` varchar(20) DEFAULT '#d4af37',
        `dietary_preference` varchar(50) DEFAULT 'None',
        `favorite_cuisine` varchar(50) DEFAULT 'None',
        `spiciness_level` varchar(50) DEFAULT 'Medium',
        `otp` varchar(10),
        `otp_expiry` datetime,
        PRIMARY KEY (`id`)
    )");

    // Self-healing database migrations for users table columns
    $userCols = $conn->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $missingUserCols = [
        'avatar_color' => "VARCHAR(20) DEFAULT '#d4af37' AFTER points",
        'dietary_preference' => "VARCHAR(50) DEFAULT 'None' AFTER avatar_color",
        'favorite_cuisine' => "VARCHAR(50) DEFAULT 'None' AFTER dietary_preference",
        'spiciness_level' => "VARCHAR(50) DEFAULT 'Medium' AFTER favorite_cuisine"
    ];

    foreach ($missingUserCols as $col => $definition) {
        if (!in_array($col, $userCols)) {
            $conn->exec("ALTER TABLE users ADD COLUMN `$col` $definition");
        }
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS `foods` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text,
        `price` decimal(10,2) NOT NULL DEFAULT 0,
        `rating` decimal(3,1) DEFAULT 4.5,
        `category` varchar(100) NOT NULL,
        `image_url` text,
        `delivery_time` varchar(50) DEFAULT '25-35 min',
        PRIMARY KEY (`id`)
    )");

} catch(PDOException $e) {
    // We don't exit here so that scripts with fallbacks (like foods.php) can still run
    $db_connection_failed = true;
    $db_error_message = $e->getMessage();
}
?>
