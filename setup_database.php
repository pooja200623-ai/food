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
    $conn->exec("CREATE TABLE IF NOT EXISTS foods (
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
    
    // Check if table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM foods");
    if ($stmt->fetchColumn() == 0) {
        $foods = [
            // Fast Food
            ['Classic Cheeseburger', 'Fast Food', 8.99, 4.6, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', '20-25 min', 'Juicy beef patty with melted cheddar, lettuce, and tomato.'],
            ['Pepperoni Pizza', 'Fast Food', 14.50, 4.8, 'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&w=500&q=80', '35-40 min', 'Authentic Italian pizza with spicy pepperoni and mozzarella.'],
            ['Crispy Fried Chicken', 'Fast Food', 12.00, 4.5, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=500&q=80', '25-30 min', 'Golden crispy chicken wings and drumsticks with secret spices.'],
            
            // Healthy
            ['Avocado Quinoa Bowl', 'Healthy', 11.50, 4.7, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=500&q=80', '15-20 min', 'Fresh avocado, quinoa, cherry tomatoes, and microgreens.'],
            ['Grilled Salmon Salad', 'Healthy', 16.99, 4.9, 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=500&q=80', '30-40 min', 'Wild-caught salmon on a bed of fresh mixed greens and citrus dressing.'],
            
            // Asian
            ['Spicy Tuna Sushi Roll', 'Asian', 13.50, 4.8, 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', '40-45 min', 'Fresh tuna mixed with spicy mayo, wrapped in seaweed and rice.'],
            ['Pad Thai Noodles', 'Asian', 10.99, 4.6, 'https://images.unsplash.com/photo-1559314809-0d155014e29e?auto=format&fit=crop&w=500&q=80', '25-35 min', 'Stir-fried rice noodles with tofu, peanuts, and tamarind sauce.'],
            
            // Desserts
            ['Chocolate Lava Cake', 'Desserts', 6.50, 4.9, 'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?auto=format&fit=crop&w=500&q=80', '15-20 min', 'Rich chocolate cake with a molten chocolate center.'],
            ['Strawberry Cheesecake', 'Desserts', 7.99, 4.7, 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=500&q=80', '15-20 min', 'Classic New York style cheesecake with fresh strawberry topping.']
        ];
        
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
