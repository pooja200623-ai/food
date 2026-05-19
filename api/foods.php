<?php
header('Content-Type: application/json');

require_once 'config.php';

$action   = isset($_GET['action'])   ? $_GET['action']   : 'all';
$category = isset($_GET['category']) ? $_GET['category'] : '';

try {
    if (isset($db_connection_failed) && $db_connection_failed) {
        throw new PDOException("Database connection failed from config: " . $db_error_message);
    }

    // Auto-seed or re-seed the foods table if it has less than the required 70 items
    $count = $conn->query("SELECT COUNT(*) FROM foods")->fetchColumn();
    if ((int)$count < 70) {
        $conn->exec("TRUNCATE TABLE foods");
        require_once '../food_data.php';
        $insert = $conn->prepare(
            "INSERT INTO foods (name, description, price, rating, category, image_url, delivery_time)
             VALUES (:name, :description, :price, :rating, :category, :image_url, :delivery_time)"
        );
        $conn->beginTransaction();
        foreach ($global_foods as $food) {
            $insert->execute([
                ':name'          => $food['name'],
                ':description'   => $food['description'],
                ':price'         => $food['price'],
                ':rating'        => $food['rating'],
                ':category'      => $food['category'],
                ':image_url'     => $food['image_url'],
                ':delivery_time' => $food['delivery_time'],
            ]);
        }
        $conn->commit();
    }

    if ($action === 'category' && $category !== '') {
        $stmt = $conn->prepare("SELECT * FROM foods WHERE category = ? ORDER BY rating DESC");
        $stmt->execute([$category]);
    } else {
        $stmt = $conn->query("SELECT * FROM foods ORDER BY rating DESC");
    }

    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $foods]);

} catch (PDOException $e) {
    // Fallback to static data when DB is not available
    require_once '../food_data.php';

    $fallbackFoods = [];
    foreach ($global_foods as $index => $food) {
        $food['id'] = $index + 1;
        $fallbackFoods[] = $food;
    }

    if ($action === 'category' && $category !== '') {
        $filtered = array_values(array_filter($fallbackFoods, function ($f) use ($category) {
            return strtolower($f['category']) === strtolower($category);
        }));
        echo json_encode(['success' => true, 'data' => $filtered, 'fallback' => true]);
    } else {
        echo json_encode(['success' => true, 'data' => $fallbackFoods, 'fallback' => true]);
    }
}
?>
