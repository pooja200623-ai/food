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
            ['name'=>'Classic Cheeseburger', 'category'=>'American', 'price'=>199, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Juicy beef patty with melted cheddar, lettuce, and tomato.'],
            ['name'=>'BBQ Pork Ribs', 'category'=>'American', 'price'=>549, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-40 min', 'description'=>'Slow-cooked ribs glazed with smoky barbecue sauce.'],
            ['name'=>'Pepperoni Pizza', 'category'=>'Fast Food', 'price'=>349, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-40 min', 'description'=>'Authentic Italian pizza with spicy pepperoni.'],
            ['name'=>'Crispy Fried Chicken', 'category'=>'Fast Food', 'price'=>249, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Golden brown crispy chicken pieces with special seasoning.'],
            ['name'=>'Chicken Biryani', 'category'=>'Indian', 'price'=>320, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-40 min', 'description'=>'Aromatic basmati rice cooked with tender chicken and authentic spices.'],
            ['name'=>'Paneer Butter Masala', 'category'=>'Indian', 'price'=>280, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1631452180519-c014fe946bc0?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-35 min', 'description'=>'Cottage cheese cubes cooked in a rich, creamy tomato gravy.'],
            ['name'=>'Bibimbap Bowl', 'category'=>'Korean', 'price'=>420, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1553163147-622ab57be1c7?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Warm rice topped with sauteed vegetables, chili paste, and egg.'],
            ['name'=>'Korean Fried Chicken', 'category'=>'Korean', 'price'=>399, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1588675459345-d41cfa2fcb6c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-35 min', 'description'=>'Double-fried chicken coated in a sweet and spicy sticky sauce.'],
            ['name'=>'Spicy Sushi Roll', 'category'=>'Japanese', 'price'=>399, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'40-45 min', 'description'=>'Fresh tuna mixed with spicy mayo, wrapped in seaweed.'],
            ['name'=>'Tonkotsu Ramen', 'category'=>'Japanese', 'price'=>450, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1557872943-16a5ac26437e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-40 min', 'description'=>'Rich pork broth noodles topped with sliced pork belly and soft egg.'],
            ['name'=>'Steamed Momos', 'category'=>'Chinese', 'price'=>149, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1625220194771-7ebdea0b70b9?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Delicate dumplings filled with minced chicken and herbs.'],
            ['name'=>'Hakka Noodles', 'category'=>'Chinese', 'price'=>199, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Wok-tossed noodles with shredded vegetables and soy sauce.'],
            ['name'=>'Chicken Fried Rice', 'category'=>'Chinese', 'price'=>220, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Classic fried rice mixed with egg, tender chicken, and veggies.'],
            ['name'=>'Grilled Salmon Salad', 'category'=>'Healthy', 'price'=>499, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Premium grilled salmon on a bed of fresh mixed greens.'],
            ['name'=>'Chocolate Lava Cake', 'category'=>'Desserts', 'price'=>149, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Rich chocolate cake with a molten chocolate center.']
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
