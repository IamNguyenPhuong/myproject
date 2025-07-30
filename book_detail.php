<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/reviews.php';

// Lấy ID sách từ URL
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    redirect('books.php');
}

// Lấy thông tin sách
$book = getBookById($conn, $book_id);

if (!$book) {
    redirect('books.php');
}

// Xử lý mượn sách
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_book'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $return_date = date('Y-m-d', strtotime('+14 days')); // Mượn 14 ngày
    
    if (borrowBook($conn, $user_id, $book_id, $return_date)) {
        $success_message = "Mượn sách thành công! Vui lòng trả sách trước ngày " . date('d/m/Y', strtotime($return_date));
    } else {
        $error_message = "Không thể mượn sách. Vui lòng thử lại sau.";
    }
}

// Lấy sách liên quan
$related_books = getRelatedBooks($conn, $book_id, $book['category'], 4);

// Khởi tạo hệ thống đánh giá
$review_system = new BookReview($conn);

// Xử lý thêm/cập nhật đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5) {
        // Kiểm tra xem user đã đánh giá chưa
        $existing_review = $review_system->getUserReview($_SESSION['user_id'], $book_id);
        
        if ($existing_review) {
            // Cập nhật đánh giá
            if ($review_system->updateReview($existing_review['id'], $rating, $comment)) {
                $success_message = "Cập nhật đánh giá thành công!";
            } else {
                $error_message = "Không thể cập nhật đánh giá. Vui lòng thử lại.";
            }
        } else {
            // Thêm đánh giá mới
            if ($review_system->addReview($_SESSION['user_id'], $book_id, $rating, $comment)) {
                $success_message = "Thêm đánh giá thành công!";
            } else {
                $error_message = "Không thể thêm đánh giá. Vui lòng thử lại.";
            }
        }
    } else {
        $error_message = "Vui lòng chọn đánh giá từ 1-5 sao.";
    }
}

// Lấy đánh giá của sách
$reviews = $review_system->getBookReviews($book_id, 10);
$user_review = null;
if (isLoggedIn()) {
    $user_review = $review_system->getUserReview($_SESSION['user_id'], $book_id);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Thư viện Online</title>
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
                        <a class="nav-link" href="books.php">Sách</a>
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

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="books.php">Sách</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($book['title']); ?></li>
            </ol>
        </nav>

        <!-- Alerts -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Book Details -->
        <div class="row">
            <!-- Book Image -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo $book['cover_image'] ? 'uploads/' . $book['cover_image'] : 'assets/images/default-book.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>"
                         style="height: 400px; object-fit: cover;">
                </div>
            </div>

            <!-- Book Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title text-primary"><?php echo htmlspecialchars($book['title']); ?></h1>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong><i class="fas fa-user me-2"></i>Tác giả:</strong>
                                    <span class="text-muted"><?php echo htmlspecialchars($book['author']); ?></span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-tag me-2"></i>Thể loại:</strong>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($book['category']); ?></span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-barcode me-2"></i>ISBN:</strong>
                                    <span class="text-muted"><?php echo htmlspecialchars($book['isbn']); ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong><i class="fas fa-book me-2"></i>Tổng số:</strong>
                                    <span class="text-muted"><?php echo $book['quantity']; ?> cuốn</span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-check-circle me-2"></i>Có sẵn:</strong>
                                    <span class="text-<?php echo $book['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $book['available_quantity']; ?> cuốn
                                    </span>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-calendar me-2"></i>Ngày thêm:</strong>
                                    <span class="text-muted"><?php echo date('d/m/Y', strtotime($book['created_at'])); ?></span>
                                </p>
                            </div>
                        </div>

                        <hr>

                        <h5><i class="fas fa-info-circle me-2"></i>Mô tả</h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>

                        <hr>

                        <!-- Borrow Button -->
                        <?php if (isLoggedIn()): ?>
                            <?php if ($book['available_quantity'] > 0): ?>
                                <form method="POST" class="d-inline">
                                    <button type="submit" name="borrow_book" class="btn btn-primary btn-lg">
                                        <i class="fas fa-bookmark me-2"></i>Mượn sách
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-times-circle me-2"></i>Hết sách
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để mượn sách
                            </a>
                        <?php endif; ?>

                        <a href="books.php" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-5">
            <h3 class="mb-4">
                <i class="fas fa-star me-2"></i>Đánh giá và bình luận
                <?php if ($book['avg_rating'] > 0): ?>
                    <span class="badge bg-warning text-dark ms-2">
                        <?php echo number_format($book['avg_rating'], 1); ?> ⭐ (<?php echo $book['review_count']; ?> đánh giá)
                    </span>
                <?php endif; ?>
            </h3>

            <!-- Add Review Form -->
            <?php if (isLoggedIn()): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <?php if ($user_review): ?>
                                <i class="fas fa-edit me-2"></i>Cập nhật đánh giá của bạn
                            <?php else: ?>
                                <i class="fas fa-plus me-2"></i>Thêm đánh giá
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Đánh giá:</label>
                                <div class="rating-input">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                               id="star<?php echo $i; ?>" 
                                               <?php echo ($user_review && $user_review['rating'] == $i) ? 'checked' : ''; ?>>
                                        <label for="star<?php echo $i; ?>">☆</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Bình luận (tùy chọn):</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" 
                                          placeholder="Chia sẻ cảm nhận của bạn về cuốn sách này..."><?php echo $user_review ? htmlspecialchars($user_review['comment']) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                <?php echo $user_review ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <a href="login.php">Đăng nhập</a> để thêm đánh giá cho cuốn sách này.
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <?php if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></h6>
                                        <div class="rating-display">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                            <span class="ms-2 text-muted"><?php echo $review['rating']; ?>/5</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có đánh giá nào</h5>
                    <p class="text-muted">Hãy là người đầu tiên đánh giá cuốn sách này!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Related Books -->
        <?php if (!empty($related_books)): ?>
            <div class="mt-5">
                <h3 class="mb-4">
                    <i class="fas fa-book-open me-2"></i>Sách liên quan
                </h3>
                <div class="row">
                    <?php foreach ($related_books as $related_book): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 book-card">
                                <img src="<?php echo $related_book['cover_image'] ? 'uploads/' . $related_book['cover_image'] : 'assets/images/default-book.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($related_book['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($related_book['title']); ?></h6>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($related_book['author']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?php echo $related_book['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $related_book['available_quantity'] > 0 ? 'Có sẵn' : 'Hết sách'; ?>
                                        </span>
                                        <a href="book_detail.php?id=<?php echo $related_book['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-book-open me-2"></i>Thư viện Online</h5>
                    <p class="mb-0">Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 Thư viện Online. Tất cả quyền được bảo lưu.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html> 