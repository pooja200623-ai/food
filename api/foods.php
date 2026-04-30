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
        ['id'=>1, 'name'=>'Classic Cheeseburger', 'category'=>'Fast Food', 'price'=>8.99, 'rating'=>4.6, 'image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'20-25 min', 'description'=>'Juicy beef patty with melted cheddar, lettuce, and tomato.'],
        ['id'=>2, 'name'=>'Pepperoni Pizza', 'category'=>'Fast Food', 'price'=>14.50, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1628840042765-356cda07504e?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'35-40 min', 'description'=>'Authentic Italian pizza with spicy pepperoni.'],
        ['id'=>4, 'name'=>'Avocado Quinoa Bowl', 'category'=>'Healthy', 'price'=>11.50, 'rating'=>4.7, 'image_url'=>'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Fresh avocado, quinoa, cherry tomatoes, and microgreens.'],
        ['id'=>6, 'name'=>'Spicy Tuna Sushi Roll', 'category'=>'Asian', 'price'=>13.50, 'rating'=>4.8, 'image_url'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'40-45 min', 'description'=>'Fresh tuna mixed with spicy mayo, wrapped in seaweed.'],
        ['id'=>8, 'name'=>'Chocolate Lava Cake', 'category'=>'Desserts', 'price'=>6.50, 'rating'=>4.9, 'image_url'=>'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?auto=format&fit=crop&w=500&q=80', 'delivery_time'=>'15-20 min', 'description'=>'Rich chocolate cake with a molten chocolate center.']
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
