<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Xử lý trả sách
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_book'])) {
    $borrowing_id = (int)$_POST['borrowing_id'];
    
    if (returnBook($conn, $borrowing_id)) {
        $success_message = "Trả sách thành công!";
    } else {
        $error_message = "Không thể trả sách. Vui lòng thử lại sau.";
    }
}

// Lấy danh sách sách đã mượn
$borrowings = getUserBorrowings($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sách của tôi - Thư viện Online</title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="my_books.php">Sách của tôi</a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/">Quản trị</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-bookmark me-2"></i>Sách của tôi
                </h1>

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

                <?php if (empty($borrowings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">Bạn chưa mượn sách nào</h3>
                        <p class="text-muted">Hãy khám phá thư viện và mượn những cuốn sách hay!</p>
                        <a href="books.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Tìm sách
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($borrowings as $borrowing): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo $borrowing['cover_image'] ? 'uploads/' . $borrowing['cover_image'] : 'assets/images/default-book.jpg'; ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($borrowing['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($borrowing['title']); ?></h5>
                                        <p class="card-text text-muted"><?php echo htmlspecialchars($borrowing['author']); ?></p>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <strong>Ngày mượn:</strong> <?php echo date('d/m/Y', strtotime($borrowing['borrow_date'])); ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-check me-1"></i>
                                                <strong>Hạn trả:</strong> <?php echo date('d/m/Y', strtotime($borrowing['return_date'])); ?>
                                            </small>
                                        </div>

                                        <?php
                                        $return_date = new DateTime($borrowing['return_date']);
                                        $today = new DateTime();
                                        $days_remaining = $today->diff($return_date)->days;
                                        $is_overdue = $return_date < $today;
                                        ?>

                                        <div class="mb-3">
                                            <?php if ($is_overdue): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Quá hạn <?php echo $days_remaining; ?> ngày
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-<?php echo $days_remaining <= 3 ? 'warning' : 'success'; ?>">
                                                    <i class="fas fa-clock me-1"></i>Còn <?php echo $days_remaining; ?> ngày
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <a href="book_detail.php?id=<?php echo $borrowing['book_id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                                            </a>
                                            
                                            <?php if ($borrowing['status'] == 'borrowed'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="borrowing_id" value="<?php echo $borrowing['id']; ?>">
                                                    <button type="submit" name="return_book" class="btn btn-success btn-sm w-100" 
                                                            onclick="return confirm('Bạn có chắc muốn trả sách này?')">
                                                        <i class="fas fa-undo me-1"></i>Trả sách
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Đã trả</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Statistics -->
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                    <h5 class="card-title">Tổng số sách đã mượn</h5>
                                    <p class="card-text display-6"><?php echo count($borrowings); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                    <h5 class="card-title">Đang mượn</h5>
                                    <p class="card-text display-6">
                                        <?php echo count(array_filter($borrowings, function($b) { return $b['status'] == 'borrowed'; })); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <h5 class="card-title">Đã trả</h5>
                                    <p class="card-text display-6">
                                        <?php echo count(array_filter($borrowings, function($b) { return $b['status'] == 'returned'; })); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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