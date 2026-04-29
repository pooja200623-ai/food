<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : 'all';

try {
    if ($action === 'all') {
        $stmt = $conn->query("SELECT * FROM restaurants ORDER BY rating DESC");
        $restaurants = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $restaurants]);
        
    } elseif ($action === 'category') {
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE category = ? ORDER BY rating DESC");
        $stmt->execute([$category]);
        $restaurants = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $restaurants]);
        
    } elseif ($action === 'menu') {
        $restaurant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Get restaurant details
        $stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurant_id]);
        $restaurant = $stmt->fetch();
        
        if ($restaurant) {
            // Get menu items
            $menuStmt = $conn->prepare("SELECT * FROM menu_items WHERE restaurant_id = ?");
            $menuStmt->execute([$restaurant_id]);
            $menu = $menuStmt->fetchAll();
            
            echo json_encode(['success' => true, 'restaurant' => $restaurant, 'menu' => $menu]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Restaurant not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
