<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Crave App - Database Diagnostics</h1>";

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'custom_app_db';
$ports = ['3306', '3307', '3308']; // Common XAMPP ports

echo "<h3>1. System Environment</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

echo "<h3>2. Testing Connections</h3>";

foreach ($ports as $port) {
    echo "<strong>Testing Port $port:</strong> ";
    try {
        $conn = new PDO("mysql:host=$host;port=$port", $user, $pass);
        echo "<span style='color:green;'>SUCCESS!</span> (Connected to MySQL Server)<br>";
        
        // Test Database existence
        $stmt = $conn->query("SHOW DATABASES LIKE '$db'");
        if ($stmt->rowCount() > 0) {
            echo "&nbsp;&nbsp; -> Database '$db': <span style='color:green;'>FOUND</span><br>";
        } else {
            echo "&nbsp;&nbsp; -> Database '$db': <span style='color:orange;'>NOT FOUND</span> (Attempting to create...)<br>";
            $conn->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4");
            echo "&nbsp;&nbsp; -> Status: <span style='color:green;'>Created successfully.</span><br>";
        }
        
        // Final verification
        $conn->exec("USE `$db` text");
        echo "&nbsp;&nbsp; -> Final Connection: <span style='color:green;'>FULLY OPERATIONAL</span><br>";
        
    } catch (PDOException $e) {
        echo "<span style='color:red;'>FAILED</span> (" . $e->getMessage() . ")<br>";
    }
}

echo "<h3>3. Fix Instructions</h3>";
echo "<ul>
    <li>If <b>Port 3306</b> says FAILED but <b>3307</b> says SUCCESS, open <b>api/config.php</b> and change the port number.</li>
    <li>If all ports say <b>'Connection refused'</b>, your MySQL is NOT running. Open XAMPP and click 'Start' next to MySQL.</li>
    <li>If it says <b>'Access denied'</b>, your MySQL user or password in config.php is incorrect.</li>
</ul>";
?>
