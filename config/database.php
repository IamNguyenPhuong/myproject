<?php
// Database Configuration
// For production, use environment variables or update these values
$dsn = 'sqlite:' . __DIR__ . '/../database/mydb.sqlite';
$conn = new PDO($dsn);

?> 
