<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/reviews.php';

$review_system = new BookReview($conn);

// Lấy sách được đánh giá cao nhất
$top_books = $review_system->getTopRatedBooks(20);

// Lấy thống kê
$total_books = count($top_books);
$avg_rating = 0;
if ($total_books > 0) {
    $total_rating = array_sum(array_column($top_books, 'avg_rating'));
    $avg_rating = $total_rating / $total_books;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sách được đánh giá cao nhất - Thư viện Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <!-- Hero Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body text-center py-5">
                        <h1 class="display-4 mb-3">
                            <i class="fas fa-star me-3"></i>Sách được đánh giá cao nhất
                        </h1>
                        <p class="lead mb-4">Khám phá những cuốn sách được độc giả yêu thích nhất</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <h3><?php echo $total_books; ?></h3>
                                    <p>Cuốn sách</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <h3><?php echo number_format($avg_rating, 1); ?> ⭐</h3>
                                    <p>Đánh giá trung bình</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <h3><?php echo array_sum(array_column($top_books, 'review_count')); ?></h3>
                                    <p>Tổng đánh giá</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Grid -->
        <?php if (!empty($top_books)): ?>
            <div class="row">
                <?php foreach ($top_books as $index => $book): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 book-card position-relative">
                            <!-- Rank Badge -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($index < 3): ?>
                                    <span class="badge bg-warning text-dark fs-6">
                                        <i class="fas fa-trophy me-1"></i>#<?php echo $index + 1; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary fs-6">#<?php echo $index + 1; ?></span>
                                <?php endif; ?>
                            </div>

                            <img src="<?php echo $book['cover_image'] ? 'uploads/' . $book['cover_image'] : 'assets/images/default-book.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 style="height: 250px; object-fit: cover;">
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($book['author']); ?></p>
                                
                                <!-- Rating Display -->
                                <div class="mb-3">
                                    <div class="rating-display mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= round($book['avg_rating']) ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                        <span class="ms-2 fw-bold"><?php echo number_format($book['avg_rating'], 1); ?></span>
                                    </div>
                                    <small class="text-muted"><?php echo $book['review_count']; ?> đánh giá</small>
                                </div>

                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-<?php echo $book['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $book['available_quantity'] > 0 ? 'Có sẵn' : 'Hết sách'; ?>
                                        </span>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($book['category']); ?></span>
                                    </div>
                                    <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-star fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">Chưa có đánh giá nào</h3>
                <p class="text-muted">Hãy đánh giá sách để giúp người khác tìm được sách hay!</p>
                <a href="books.php" class="btn btn-primary">
                    <i class="fas fa-book me-2"></i>Xem tất cả sách
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 