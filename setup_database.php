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
            ['name'=>'Classic Cheeseburger', 'category'=>'Fast Food', 'price'=>199, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Juicy beef patty with melted cheddar, lettuce, and tomato.'],
            ['name'=>'Pepperoni Pizza', 'category'=>'Fast Food', 'price'=>349, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-40 min', 'description'=>'Authentic Italian pizza with spicy pepperoni.'],
            ['name'=>'Crispy Fried Chicken', 'category'=>'Fast Food', 'price'=>249, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Golden brown crispy chicken pieces with special seasoning.'],
            ['name'=>'Avocado Quinoa Bowl', 'category'=>'Healthy', 'price'=>229, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Fresh avocado, quinoa, cherry tomatoes, and microgreens.'],
            ['name'=>'Grilled Salmon Salad', 'category'=>'Healthy', 'price'=>499, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Premium grilled salmon on a bed of fresh mixed greens.'],
            ['name'=>'Spicy Tuna Sushi Roll', 'category'=>'Asian', 'price'=>399, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'40-45 min', 'description'=>'Fresh tuna mixed with spicy mayo, wrapped in seaweed.'],
            ['name'=>'Chicken Tikka Masala', 'category'=>'Asian', 'price'=>299, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-35 min', 'description'=>'Roasted marinated chicken chunks in a spiced curry sauce.'],
            ['name'=>'Pad Thai Noodles', 'category'=>'Asian', 'price'=>279, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1559314809-0d155014e29e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Stir-fried rice noodles with eggs, peanuts, and bean sprouts.'],
            ['name'=>'Chocolate Lava Cake', 'category'=>'Desserts', 'price'=>149, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Rich chocolate cake with a molten chocolate center.'],
            ['name'=>'Strawberry Cheesecake', 'category'=>'Desserts', 'price'=>179, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Classic New York style cheesecake with strawberry compote.'],
            ['name'=>'Mango Lassi', 'category'=>'Healthy', 'price'=>99, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'10-15 min', 'description'=>'Refreshing yogurt-based drink made with fresh sweet mangoes.']
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
