<?php
// Database Configuration
// For production, use environment variables or update these values
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'library_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Production settings
$options = [];

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
