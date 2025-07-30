<?php
try {
    $dsn = 'sqlite:' . __DIR__ . '/../database/mydb.sqlite';
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("SQLite connection failed: " . $e->getMessage());
}
?>
