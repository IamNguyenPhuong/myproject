<?php
require_once __DIR__ . '/../config/config.php';

// User functions
function registerUser($conn, $username, $email, $password, $full_name) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, created_at) VALUES (?, ?, ?, ?, 'user', NOW())");
    return $stmt->execute([$username, $email, $hashed_password, $full_name]);
}

function loginUser($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        return true;
    }
    return false;
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Book functions
function getAllBooks($conn, $limit = null, $offset = 0) {
    $sql = "SELECT * FROM books ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function getRecentBooks($conn, $limit = 6) {
    $stmt = $conn->prepare("SELECT * FROM books ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getBookById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function searchBooks($conn, $keyword) {
    $keyword = "%$keyword%";
    $stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $stmt->execute([$keyword, $keyword, $keyword]);
    return $stmt->fetchAll();
}

function addBook($conn, $title, $author, $description, $isbn, $category, $quantity, $cover_image = null) {
    $stmt = $conn->prepare("INSERT INTO books (title, author, description, isbn, category, quantity, available_quantity, cover_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$title, $author, $description, $isbn, $category, $quantity, $quantity, $cover_image]);
}

function updateBook($conn, $id, $title, $author, $description, $isbn, $category, $quantity, $cover_image = null) {
    $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, description = ?, isbn = ?, category = ?, quantity = ?, cover_image = ? WHERE id = ?");
    return $stmt->execute([$title, $author, $description, $isbn, $category, $quantity, $cover_image, $id]);
}

function deleteBook($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    return $stmt->execute([$id]);
}

function getRelatedBooks($conn, $exclude_id, $category, $limit = 4) {
    try {
        $stmt = $conn->prepare("SELECT * FROM books WHERE category = ? AND id != ? AND available_quantity > 0 ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $category, PDO::PARAM_STR);
        $stmt->bindValue(2, $exclude_id, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Borrow functions
function borrowBook($conn, $user_id, $book_id, $return_date) {
    // Check if book is available
    $book = getBookById($conn, $book_id);
    if (!$book || $book['available_quantity'] <= 0) {
        return false;
    }
    
    // Check if user already borrowed this book
    $stmt = $conn->prepare("SELECT * FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
    $stmt->execute([$user_id, $book_id]);
    if ($stmt->fetch()) {
        return false;
    }
    
    $conn->beginTransaction();
    try {
        // Create borrowing record
        $stmt = $conn->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, return_date, status) VALUES (?, ?, NOW(), ?, 'borrowed')");
        $stmt->execute([$user_id, $book_id, $return_date]);
        
        // Update book availability
        $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?");
        $stmt->execute([$book_id]);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function returnBook($conn, $borrowing_id) {
    $conn->beginTransaction();
    try {
        // Get borrowing info
        $stmt = $conn->prepare("SELECT * FROM borrowings WHERE id = ?");
        $stmt->execute([$borrowing_id]);
        $borrowing = $stmt->fetch();
        
        if (!$borrowing) {
            return false;
        }
        
        // Update borrowing status
        $stmt = $conn->prepare("UPDATE borrowings SET status = 'returned', actual_return_date = NOW() WHERE id = ?");
        $stmt->execute([$borrowing_id]);
        
        // Update book availability
        $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?");
        $stmt->execute([$borrowing['book_id']]);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

function getUserBorrowings($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT b.*, bk.title, bk.author, bk.cover_image 
        FROM borrowings b 
        JOIN books bk ON b.book_id = bk.id 
        WHERE b.user_id = ? 
        ORDER BY b.borrow_date DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getAllBorrowings($conn) {
    $stmt = $conn->prepare("
        SELECT b.*, u.username, u.full_name, bk.title, bk.author 
        FROM borrowings b 
        JOIN users u ON b.user_id = u.id 
        JOIN books bk ON b.book_id = bk.id 
        ORDER BY b.borrow_date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Utility functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

function uploadImage($file, $target_dir = "uploads/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $target_path = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    }
    
    return false;
}

// Security functions
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function logActivity($user_id, $action, $details = '') {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function checkRateLimit($action, $limit = 10, $window = 3600) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip_address = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$ip, $action, $window]);
        $count = $stmt->fetchColumn();
        
        if ($count >= $limit) {
            return false;
        }
        
        $stmt = $conn->prepare("INSERT INTO rate_limits (ip_address, action, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$ip, $action]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Rate limit check failed: " . $e->getMessage());
        return true; // Allow if rate limiting fails
    }
}
?> 