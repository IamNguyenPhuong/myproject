<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-primary">Chào mừng đến với Thư viện Online</h1>
                    <p class="lead">Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay. Đăng ký ngay để bắt đầu hành trình đọc sách của bạn!</p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký ngay
                        </a>
                        <a href="books.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-book me-2"></i>Xem sách
                        </a>
                    <?php else: ?>
                        <a href="books.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Tìm sách
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/library-hero.jpg" alt="Thư viện" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Tính năng nổi bật</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Tìm kiếm sách</h5>
                        <p class="card-text">Tìm kiếm sách theo tên, tác giả, thể loại một cách dễ dàng và nhanh chóng.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-bookmark fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Mượn sách online</h5>
                        <p class="card-text">Đặt mượn sách trực tuyến, theo dõi lịch sử mượn trả của bạn.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Thống kê chi tiết</h5>
                        <p class="card-text">Xem thống kê về sách phổ biến, hoạt động mượn trả và báo cáo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Books Section -->
    <div class="container my-5">
        <h2 class="text-center mb-5">Sách mới nhất</h2>
        <div class="row">
            <?php
            $recent_books = getRecentBooks($conn, 6);
            foreach ($recent_books as $book):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="<?php echo $book['cover_image'] ? 'uploads/' . $book['cover_image'] : 'assets/images/default-book.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>"
                         style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text text-muted">Tác giả: <?php echo htmlspecialchars($book['author']); ?></p>
                        
                        <!-- Rating Display -->
                        <?php if ($book['avg_rating'] > 0): ?>
                            <div class="mb-2">
                                <div class="rating-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= round($book['avg_rating']) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                    <span class="ms-1 small text-muted"><?php echo number_format($book['avg_rating'], 1); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php echo $book['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                <?php echo $book['available_quantity'] > 0 ? 'Có sẵn' : 'Hết sách'; ?>
                            </span>
                            <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="books.php" class="btn btn-primary">Xem tất cả sách</a>
        </div>
    </div>

    <!-- Top Rated Books Section -->
    <?php
    require_once 'includes/reviews.php';
    $review_system = new BookReview($conn);
    $top_books = $review_system->getTopRatedBooks(6);
    ?>
    <?php if (!empty($top_books)): ?>
        <div class="container my-5">
            <h2 class="text-center mb-5">
                <i class="fas fa-star me-2"></i>Sách được đánh giá cao nhất
            </h2>
            <div class="row">
                <?php foreach ($top_books as $book): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo $book['cover_image'] ? 'uploads/' . $book['cover_image'] : 'assets/images/default-book.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted">Tác giả: <?php echo htmlspecialchars($book['author']); ?></p>
                                
                                <!-- Rating Display -->
                                <div class="mb-2">
                                    <div class="rating-display">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= round($book['avg_rating']) ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                        <span class="ms-1 fw-bold"><?php echo number_format($book['avg_rating'], 1); ?></span>
                                    </div>
                                    <small class="text-muted"><?php echo $book['review_count']; ?> đánh giá</small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-<?php echo $book['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $book['available_quantity'] > 0 ? 'Có sẵn' : 'Hết sách'; ?>
                                    </span>
                                    <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-star me-1"></i>Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="top_rated_books.php" class="btn btn-warning">Xem tất cả sách được đánh giá cao</a>
            </div>
        </div>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 