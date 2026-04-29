<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect without database selected first
    $conn = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $sql = "CREATE DATABASE IF NOT EXISTS foodiehub_db";
    $conn->exec($sql);
    echo "Database created successfully or already exists.<br>";

    // Select the database
    $conn->exec("USE foodiehub_db");

    // Create Users Table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        otp VARCHAR(10),
        otp_expiry DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Users table created successfully.<br>";

    // Create Restaurants Table
    $sql = "CREATE TABLE IF NOT EXISTS restaurants (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        category VARCHAR(50),
        rating DECIMAL(3,1) DEFAULT 0.0,
        delivery_time VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Restaurants table created successfully.<br>";

    // Create Menu Items Table
    $sql = "CREATE TABLE IF NOT EXISTS menu_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT(11) NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255),
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Menu Items table created successfully.<br>";

    // Create Orders Table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Orders table created successfully.<br>";

    // Create Order Items Table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) NOT NULL,
        menu_item_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Order Items table created successfully.<br>";

    // Insert Dummy Data for Restaurants if empty
    $stmt = $conn->query("SELECT COUNT(*) FROM restaurants");
    if ($stmt->fetchColumn() == 0) {
        $restaurants = [
            ['Spicy Bite', 'Authentic Indian cuisine', 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=400&q=80', 'Indian', 4.5, '30-45 min'],
            ['Burger Lounge', 'Juicy gourmet burgers', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&q=80', 'Burger', 4.2, '20-30 min'],
            ['Sushi Zen', 'Fresh and authentic sushi', 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=400&q=80', 'Sushi', 4.8, '40-55 min'],
            ['Pizza Hut', 'Classic and specialty pizzas', 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=400&q=80', 'Pizza', 4.1, '25-40 min'],
            ['Taco Fiesta', 'Mexican street food', 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=400&q=80', 'Mexican', 4.6, '20-35 min'],
            ['Green Bowl', 'Healthy salads and bowls', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=400&q=80', 'Healthy', 4.3, '15-25 min']
        ];

        $stmt = $conn->prepare("INSERT INTO restaurants (name, description, image_url, category, rating, delivery_time) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($restaurants as $res) {
            $stmt->execute($res);
        }
        echo "Sample restaurants inserted.<br>";

        // Insert Dummy Data for Menu Items
        $menuItems = [
            // Spicy Bite (id=1)
            [1, 'Chicken Tikka Masala', 'Creamy and spicy chicken curry', 12.99, 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=300&q=80'],
            [1, 'Garlic Naan', 'Freshly baked flatbread with garlic', 2.99, 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=300&q=80'],
            // Burger Lounge (id=2)
            [2, 'Classic Cheeseburger', 'Beef patty, cheese, lettuce, tomato', 8.99, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=300&q=80'],
            [2, 'Crispy Fries', 'Golden french fries', 3.49, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=300&q=80'],
            // Sushi Zen (id=3)
            [3, 'Spicy Tuna Roll', 'Fresh tuna with spicy mayo', 9.50, 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=300&q=80'],
            [3, 'Salmon Sashimi', '6 pieces of fresh salmon', 14.00, 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=300&q=80'],
            // Pizza Hut (id=4)
            [4, 'Margherita Pizza', 'Classic tomato and mozzarella', 10.99, 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=300&q=80'],
            [4, 'Pepperoni Pizza', 'Loaded with pepperoni', 13.99, 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=300&q=80'],
            // Taco Fiesta (id=5)
            [5, 'Street Tacos (3)', 'Carne asada with onions and cilantro', 7.99, 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=300&q=80'],
            [5, 'Nachos Supreme', 'Chips loaded with cheese, beans, meat', 9.99, 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=300&q=80'],
            // Green Bowl (id=6)
            [6, 'Quinoa Salad', 'Quinoa, veggies, lemon vinaigrette', 11.50, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=300&q=80'],
            [6, 'Acai Bowl', 'Acai berries with granola and fruit', 8.50, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=300&q=80']
        ];
        
        $stmt = $conn->prepare("INSERT INTO menu_items (restaurant_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?)");
        foreach ($menuItems as $item) {
            $stmt->execute($item);
        }
        echo "Sample menu items inserted.<br>";
    }

    echo "<h1>Database Setup Complete!</h1>";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
