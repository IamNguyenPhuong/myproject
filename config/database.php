<?php
// Database Configuration
// For production, use environment variables or update these values
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'library_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Production settings
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // Log error for production (don't display sensitive info)
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show user-friendly message
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        die("Database connection error. Please contact administrator.");
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}
?> 