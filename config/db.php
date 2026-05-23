<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spac_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+8:00'");

// Prevent "MySQL server has gone away"
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$conn->query("SET SESSION wait_timeout=600");
$conn->query("SET SESSION interactive_timeout=600");

?>
