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

    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    
    if ($action === 'category') {
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $stmt = $conn->prepare("SELECT * FROM foods WHERE category = ? ORDER BY rating DESC");
        $stmt->execute([$cat]);
        $foods = $stmt->fetchAll();
    } else {
        // action = all
        $stmt = $conn->query("SELECT * FROM foods ORDER BY rating DESC");
        $foods = $stmt->fetchAll();
    }
    
    echo json_encode(['success' => true, 'data' => $foods]);

} catch(PDOException $e) {
    // Fallback static data if MySQL is not running
    $fallbackFoods = [
        ['id'=>1, 'name'=>'Classic Cheeseburger', 'category'=>'Fast Food', 'price'=>199, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Juicy beef patty with melted cheddar, lettuce, and tomato.'],
        ['id'=>2, 'name'=>'Pepperoni Pizza', 'category'=>'Fast Food', 'price'=>349, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-40 min', 'description'=>'Authentic Italian pizza with spicy pepperoni.'],
        ['id'=>3, 'name'=>'Crispy Fried Chicken', 'category'=>'Fast Food', 'price'=>249, 'rating'=>4.5, 'image_url'=>'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Golden brown crispy chicken pieces with special seasoning.'],
        ['id'=>4, 'name'=>'Avocado Quinoa Bowl', 'category'=>'Healthy', 'price'=>229, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Fresh avocado, quinoa, cherry tomatoes, and microgreens.'],
        ['id'=>5, 'name'=>'Grilled Salmon Salad', 'category'=>'Healthy', 'price'=>499, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Premium grilled salmon on a bed of fresh mixed greens.'],
        ['id'=>6, 'name'=>'Spicy Tuna Sushi Roll', 'category'=>'Asian', 'price'=>399, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'40-45 min', 'description'=>'Fresh tuna mixed with spicy mayo, wrapped in seaweed.'],
        ['id'=>7, 'name'=>'Chicken Tikka Masala', 'category'=>'Asian', 'price'=>299, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'30-35 min', 'description'=>'Roasted marinated chicken chunks in a spiced curry sauce.'],
        ['id'=>8, 'name'=>'Pad Thai Noodles', 'category'=>'Asian', 'price'=>279, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1559314809-0d155014e29e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'25-30 min', 'description'=>'Stir-fried rice noodles with eggs, peanuts, and bean sprouts.'],
        ['id'=>9, 'name'=>'Chocolate Lava Cake', 'category'=>'Desserts', 'price'=>149, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Rich chocolate cake with a molten chocolate center.'],
        ['id'=>10, 'name'=>'Strawberry Cheesecake', 'category'=>'Desserts', 'price'=>179, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Classic New York style cheesecake with strawberry compote.'],
        ['id'=>11, 'name'=>'Mango Lassi', 'category'=>'Healthy', 'price'=>99, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'10-15 min', 'description'=>'Refreshing yogurt-based drink made with fresh sweet mangoes.']
    ];
    
    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    if ($action === 'category') {
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $filtered = array_values(array_filter($fallbackFoods, function($f) use ($cat) { return $f['category'] === $cat; }));
        echo json_encode(['success' => true, 'data' => $filtered, 'fallback' => true]);
    } else {
        echo json_encode(['success' => true, 'data' => $fallbackFoods, 'fallback' => true]);
    }
}
?>
