<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Lấy thống kê tổng quan
$stats = [];

// Tổng số sách
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM books");
$stmt->execute();
$stats['total_books'] = $stmt->fetch()['total'];

// Sách có sẵn
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM books WHERE available_quantity > 0");
$stmt->execute();
$stats['available_books'] = $stmt->fetch()['total'];

// Tổng số người dùng
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['total'];

// Tổng số lượt mượn
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM borrowings");
$stmt->execute();
$stats['total_borrowings'] = $stmt->fetch()['total'];

// Sách đang được mượn
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed'");
$stmt->execute();
$stats['active_borrowings'] = $stmt->fetch()['total'];

// Sách quá hạn
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM borrowings WHERE return_date < CURDATE() AND status = 'borrowed'");
$stmt->execute();
$stats['overdue_books'] = $stmt->fetch()['total'];

// Sách được trả hôm nay
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM borrowings WHERE DATE(actual_return_date) = CURDATE()");
$stmt->execute();
$stats['returned_today'] = $stmt->fetch()['total'];

// Sách được mượn hôm nay
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM borrowings WHERE DATE(borrow_date) = CURDATE()");
$stmt->execute();
$stats['borrowed_today'] = $stmt->fetch()['total'];

// Lấy sách mượn nhiều nhất
$stmt = $conn->prepare("
    SELECT b.title, b.author, COUNT(br.id) as borrow_count
    FROM books b
    LEFT JOIN borrowings br ON b.id = br.book_id
    GROUP BY b.id
    ORDER BY borrow_count DESC
    LIMIT 5
");
$stmt->execute();
$popular_books = $stmt->fetchAll();

// Lấy người dùng mượn nhiều nhất
$stmt = $conn->prepare("
    SELECT u.username, u.full_name, COUNT(br.id) as borrow_count
    FROM users u
    LEFT JOIN borrowings br ON u.id = br.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY borrow_count DESC
    LIMIT 5
");
$stmt->execute();
$active_users = $stmt->fetchAll();

// Lấy sách quá hạn gần đây
$stmt = $conn->prepare("
    SELECT br.*, b.title, b.author, u.username, u.full_name
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.id
    WHERE br.return_date < CURDATE() AND br.status = 'borrowed'
    ORDER BY br.return_date ASC
    LIMIT 10
");
$stmt->execute();
$overdue_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Thư viện Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-book-open me-2"></i>Thư viện Online - Quản trị
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_books.php">
                            <i class="fas fa-book me-1"></i>Quản lý sách
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">
                            <i class="fas fa-users me-1"></i>Quản lý người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="borrowing_reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Báo cáo mượn sách
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php">Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="../index.php">Về trang chủ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </h1>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['total_books']; ?></h4>
                                        <p class="card-text">Tổng số sách</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['available_books']; ?></h4>
                                        <p class="card-text">Sách có sẵn</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['total_users']; ?></h4>
                                        <p class="card-text">Người dùng</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['active_borrowings']; ?></h4>
                                        <p class="card-text">Đang mượn</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-bookmark fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-plus-circle fa-2x text-success mb-2"></i>
                                <h5 class="card-title"><?php echo $stats['borrowed_today']; ?></h5>
                                <p class="card-text">Sách mượn hôm nay</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                                <h5 class="card-title"><?php echo $stats['returned_today']; ?></h5>
                                <p class="card-text">Sách trả hôm nay</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h5 class="card-title"><?php echo $stats['overdue_books']; ?></h5>
                                <p class="card-text">Sách quá hạn</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Popular Books -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-star me-2"></i>Sách mượn nhiều nhất
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($popular_books)): ?>
                                    <p class="text-muted">Chưa có dữ liệu</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tên sách</th>
                                                    <th>Tác giả</th>
                                                    <th>Số lần mượn</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($popular_books as $book): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $book['borrow_count']; ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-friends me-2"></i>Người dùng tích cực
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($active_users)): ?>
                                    <p class="text-muted">Chưa có dữ liệu</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tên đăng nhập</th>
                                                    <th>Họ tên</th>
                                                    <th>Số lần mượn</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($active_users as $user): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-success"><?php echo $user['borrow_count']; ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue Books -->
                <?php if (!empty($overdue_list)): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Sách quá hạn
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tên sách</th>
                                                    <th>Người mượn</th>
                                                    <th>Ngày mượn</th>
                                                    <th>Hạn trả</th>
                                                    <th>Số ngày quá hạn</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($overdue_list as $overdue): ?>
                                                    <?php
                                                    $return_date = new DateTime($overdue['return_date']);
                                                    $today = new DateTime();
                                                    $days_overdue = $today->diff($return_date)->days;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($overdue['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($overdue['full_name']); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($overdue['borrow_date'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($overdue['return_date'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-danger"><?php echo $days_overdue; ?> ngày</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html> 