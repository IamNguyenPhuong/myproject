<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Get books based on search and filters
if (!empty($search)) {
    $books = searchBooks($conn, $search);
} else {
    $books = getAllBooks($conn);
}

// Filter by category if specified
if (!empty($category)) {
    $books = array_filter($books, function($book) use ($category) {
        return $book['category'] == $category;
    });
}

// Sort books
switch ($sort) {
    case 'title':
        usort($books, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        break;
    case 'author':
        usort($books, function($a, $b) {
            return strcmp($a['author'], $b['author']);
        });
        break;
    case 'oldest':
        usort($books, function($a, $b) {
            return strcmp($a['created_at'], $b['created_at']);
        });
        break;
    default: // newest
        usort($books, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });
}

// Get unique categories for filter
$categories = [];
$stmt = $conn->prepare("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sách - Thư viện Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open me-2"></i>Thư viện Online
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="books.php">Sách</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_books.php">Sách của tôi</a>
                        </li>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/">Quản trị</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Hồ sơ</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold text-primary">
                    <i class="fas fa-books me-3"></i>Danh sách sách
                </h1>
                <p class="lead text-muted">Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay</p>
            </div>
            <div class="col-md-4 text-md-end">
                <p class="text-muted">Tìm thấy <strong><?php echo count($books); ?></strong> cuốn sách</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm sách, tác giả..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select filter-select" name="category">
                        <option value="">Tất cả thể loại</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select filter-select" name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                        <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Theo tên sách</option>
                        <option value="author" <?php echo $sort == 'author' ? 'selected' : ''; ?>>Theo tác giả</option>
                    </select>
                </div>
            </form>
            
            <?php if (!empty($search) || !empty($category)): ?>
                <div class="mt-3">
                    <a href="books.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-2"></i>Xóa bộ lọc
                    </a>
                    <?php if (!empty($search)): ?>
                        <span class="badge bg-primary ms-2">Tìm kiếm: "<?php echo htmlspecialchars($search); ?>"</span>
                    <?php endif; ?>
                    <?php if (!empty($category)): ?>
                        <span class="badge bg-info ms-2">Thể loại: <?php echo htmlspecialchars($category); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Books Grid -->
        <?php if (empty($books)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h3 class="text-muted">Không tìm thấy sách</h3>
                <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
                <a href="books.php" class="btn btn-primary">
                    <i class="fas fa-undo me-2"></i>Xem tất cả sách
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($books as $book): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 book-card">
                            <img src="<?php echo $book['cover_image'] ?: 'assets/images/default-book.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($book['author']); ?>
                                </p>
                                <p class="card-text flex-grow-1">
                                    <?php echo substr(htmlspecialchars($book['description']), 0, 100) . '...'; ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-<?php echo $book['available_quantity'] > 0 ? 'success' : 'danger'; ?> availability-badge">
                                            <?php echo $book['available_quantity'] > 0 ? 'Có sẵn' : 'Đã mượn'; ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-layer-group me-1"></i><?php echo $book['available_quantity']; ?>/<?php echo $book['quantity']; ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($book['category']); ?></span>
                                        <div>
                                            <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </a>
                                            <?php if (isset($_SESSION['user_id']) && $book['available_quantity'] > 0): ?>
                                                <a href="borrow_book.php?id=<?php echo $book['id']; ?>" 
                                                   class="btn btn-sm btn-primary borrow-btn">
                                                    <i class="fas fa-bookmark me-1"></i>Mượn
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Thư viện Online</h5>
                    <p>Nơi khám phá tri thức và phát triển văn hóa đọc.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 Thư viện Online. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html> 