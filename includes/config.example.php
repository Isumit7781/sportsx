<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Set your database password here
define('DB_NAME', 'sports_management');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL - update this according to your server configuration
define('BASE_URL', '/'); // Set to your base URL

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_PLAYER', 'player');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
