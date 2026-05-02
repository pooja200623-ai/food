<?php
header('Content-Type: application/json');

require_once 'config.php';

try {
    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    
    if ($action === 'category') {
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $stmt = $conn->prepare("SELECT * FROM foods WHERE category = ? ORDER BY rating DESC");
        $stmt->execute([$cat]);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // action = all
        $stmt = $conn->query("SELECT * FROM foods ORDER BY rating DESC");
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode(['success' => true, 'data' => $foods]);

} catch(PDOException $e) {
    // Fallback static data if MySQL is not running
    require_once '../food_data.php';
    
    // Transform global_foods to have 'id' (since DB would provide it)
    $fallbackFoods = [];
    foreach ($global_foods as $index => $food) {
        $food['id'] = $index + 1;
        $fallbackFoods[] = $food;
    }
    
    $action = isset($_GET['action']) ? $_GET['action'] : 'all';
    if ($action === 'category') {
        $cat = isset($_GET['category']) ? $_GET['category'] : '';
        $filtered = array_values(array_filter($fallbackFoods, function($f) use ($cat) { 
            return strtolower($f['category']) === strtolower($cat); 
        }));
        echo json_encode(['success' => true, 'data' => $filtered, 'fallback' => true]);
    } else {
        echo json_encode(['success' => true, 'data' => $fallbackFoods, 'fallback' => true]);
    }
}
?>
