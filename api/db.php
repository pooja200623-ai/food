<?php
$host = 'localhost';
$db_name = 'foodiehub_db';
$username = 'root'; // default XAMPP username
$password = ''; // default XAMPP password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If the database doesn't exist yet, we don't want the connection to die completely if we're just setting it up,
    // but for normal API calls, it should exit.
    if (strpos($_SERVER['SCRIPT_NAME'], 'setup_database.php') === false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Please run setup_database.php first.']);
        exit;
    }
}
?>
