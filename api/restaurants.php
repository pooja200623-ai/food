<?php
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : 'all';

// ----------------------------------------------------------
// Fallback sample data (used when MySQL is offline)
// ----------------------------------------------------------
$sampleRestaurants = [
    ['id'=>1,'name'=>'The Spice Haven','category'=>'Indian','rating'=>4.8,'delivery_time'=>'20-30 min','image_url'=>'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=400&q=80'],
    ['id'=>2,'name'=>'Pizza Palace','category'=>'Pizza','rating'=>4.5,'delivery_time'=>'25-35 min','image_url'=>'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=400&q=80'],
    ['id'=>3,'name'=>'Sushi Zen','category'=>'Sushi','rating'=>4.7,'delivery_time'=>'30-40 min','image_url'=>'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=400&q=80'],
    ['id'=>4,'name'=>'Burger Barn','category'=>'Burger','rating'=>4.3,'delivery_time'=>'15-25 min','image_url'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&q=80'],
    ['id'=>5,'name'=>'Taco Fiesta','category'=>'Mexican','rating'=>4.4,'delivery_time'=>'20-30 min','image_url'=>'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=400&q=80'],
    ['id'=>6,'name'=>'Mama Mia Ristorante','category'=>'Pizza','rating'=>4.6,'delivery_time'=>'35-45 min','image_url'=>'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=400&q=80'],
    ['id'=>7,'name'=>'Dragon Palace','category'=>'Chinese','rating'=>4.2,'delivery_time'=>'25-35 min','image_url'=>'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=400&q=80'],
    ['id'=>8,'name'=>'Le Petit Bistro','category'=>'French','rating'=>4.9,'delivery_time'=>'40-50 min','image_url'=>'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=400&q=80'],
    ['id'=>9,'name'=>'Bangkok Kitchen','category'=>'Thai','rating'=>4.5,'delivery_time'=>'30-40 min','image_url'=>'https://images.unsplash.com/photo-1559314809-0d155014e29e?auto=format&fit=crop&w=400&q=80'],
];

$sampleMenu = [
    ['id'=>1,'restaurant_id'=>1,'name'=>'Butter Chicken','price'=>320,'description'=>'Creamy tomato-based curry with tender chicken.','image_url'=>'https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=200&q=80'],
    ['id'=>2,'restaurant_id'=>1,'name'=>'Dal Makhani','price'=>180,'description'=>'Slow-cooked black lentils in rich spiced butter gravy.','image_url'=>'https://images.unsplash.com/photo-1546833998-877b37c2e4c6?auto=format&fit=crop&w=200&q=80'],
    ['id'=>3,'restaurant_id'=>1,'name'=>'Garlic Naan','price'=>60,'description'=>'Freshly baked fluffy flatbread with garlic and butter.','image_url'=>'https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=200&q=80'],
    ['id'=>4,'restaurant_id'=>2,'name'=>'Margherita Pizza','price'=>349,'description'=>'Classic tomato base, mozzarella and fresh basil.','image_url'=>'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=200&q=80'],
    ['id'=>5,'restaurant_id'=>2,'name'=>'BBQ Chicken Pizza','price'=>449,'description'=>'Smoky BBQ sauce, grilled chicken and red onions.','image_url'=>'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=200&q=80'],
];

// ----------------------------------------------------------
// Try DB connection
// ----------------------------------------------------------
$conn = null;
$dbAvailable = false;
try {
    $conn = new PDO("mysql:host=localhost;dbname=foodiehub_db;charset=utf8", 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbAvailable = true;
} catch(PDOException $e) {
    $dbAvailable = false;
}

// ----------------------------------------------------------
// Handle actions
// ----------------------------------------------------------
if ($action === 'all') {
    if ($dbAvailable) {
        try {
            $stmt = $conn->query("SELECT * FROM restaurants ORDER BY rating DESC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch(PDOException $e) {
            echo json_encode(['success' => true, 'data' => $sampleRestaurants]);
        }
    } else {
        echo json_encode(['success' => true, 'data' => $sampleRestaurants]);
    }

} elseif ($action === 'category') {
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    if ($dbAvailable) {
        try {
            $stmt = $conn->prepare("SELECT * FROM restaurants WHERE category = ? ORDER BY rating DESC");
            $stmt->execute([$category]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        } catch(PDOException $e) {
            $filtered = array_values(array_filter($sampleRestaurants, fn($r) => strcasecmp($r['category'], $category) === 0));
            echo json_encode(['success' => true, 'data' => $filtered]);
        }
    } else {
        $filtered = array_values(array_filter($sampleRestaurants, fn($r) => strcasecmp($r['category'], $category) === 0));
        echo json_encode(['success' => true, 'data' => count($filtered) ? $filtered : $sampleRestaurants]);
    }

} elseif ($action === 'menu') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($dbAvailable) {
        try {
            $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
            $stmt->execute([$id]);
            $restaurant = $stmt->fetch();
            if ($restaurant) {
                $menuStmt = $conn->prepare("SELECT * FROM menu_items WHERE restaurant_id = ?");
                $menuStmt->execute([$id]);
                echo json_encode(['success' => true, 'restaurant' => $restaurant, 'menu' => $menuStmt->fetchAll()]);
            } else {
                // fallback to sample
                $restaurant = $sampleRestaurants[($id - 1) % count($sampleRestaurants)];
                $menu = array_values(array_filter($sampleMenu, fn($m) => $m['restaurant_id'] == $id));
                echo json_encode(['success' => true, 'restaurant' => $restaurant, 'menu' => $menu ?: array_slice($sampleMenu, 0, 3)]);
            }
        } catch(PDOException $e) {
            $restaurant = $sampleRestaurants[($id - 1) % count($sampleRestaurants)] ?? $sampleRestaurants[0];
            echo json_encode(['success' => true, 'restaurant' => $restaurant, 'menu' => array_slice($sampleMenu, 0, 3)]);
        }
    } else {
        $idx = max(0, ($id - 1) % count($sampleRestaurants));
        $restaurant = $sampleRestaurants[$idx];
        $menu = array_values(array_filter($sampleMenu, fn($m) => $m['restaurant_id'] == $id));
        echo json_encode(['success' => true, 'restaurant' => $restaurant, 'menu' => $menu ?: array_slice($sampleMenu, 0, 3)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
