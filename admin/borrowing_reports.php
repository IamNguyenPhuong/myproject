<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Lọc theo thời gian
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Xây dựng câu query
$where_conditions = [];
$params = [];

if ($filter == 'today') {
    $where_conditions[] = "DATE(br.borrow_date) = CURDATE()";
} elseif ($filter == 'week') {
    $where_conditions[] = "br.borrow_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter == 'month') {
    $where_conditions[] = "br.borrow_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
} elseif ($filter == 'custom' && $date_from && $date_to) {
    $where_conditions[] = "DATE(br.borrow_date) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Lấy thống kê tổng quan
$stats_query = "
    SELECT 
        COUNT(*) as total_borrowings,
        COUNT(CASE WHEN br.status = 'borrowed' THEN 1 END) as active_borrowings,
        COUNT(CASE WHEN br.status = 'returned' THEN 1 END) as returned_books,
        COUNT(CASE WHEN br.return_date < CURDATE() AND br.status = 'borrowed' THEN 1 END) as overdue_books
    FROM borrowings br
    $where_clause
";
$stmt = $conn->prepare($stats_query);
$stmt->execute($params);
$stats = $stmt->fetch();

// Lấy danh sách mượn sách
$borrowings_query = "
    SELECT br.*, b.title, b.author, b.category, u.username, u.full_name, u.email
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.id
    $where_clause
    ORDER BY br.borrow_date DESC
    LIMIT 100
";
$stmt = $conn->prepare($borrowings_query);
$stmt->execute($params);
$borrowings = $stmt->fetchAll();

// Lấy sách mượn nhiều nhất
$popular_books_query = "
    SELECT b.title, b.author, b.category, COUNT(br.id) as borrow_count
    FROM books b
    LEFT JOIN borrowings br ON b.id = br.book_id
    $where_clause
    GROUP BY b.id
    ORDER BY borrow_count DESC
    LIMIT 10
";
$stmt = $conn->prepare($popular_books_query);
$stmt->execute($params);
$popular_books = $stmt->fetchAll();

// Lấy người dùng mượn nhiều nhất
$active_users_query = "
    SELECT u.username, u.full_name, u.email, COUNT(br.id) as borrow_count
    FROM users u
    LEFT JOIN borrowings br ON u.id = br.user_id
    $where_clause
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY borrow_count DESC
    LIMIT 10
";
$stmt = $conn->prepare($active_users_query);
$stmt->execute($params);
$active_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo mượn sách - Thư viện Online</title>
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
                        <a class="nav-link" href="index.php">
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
                        <a class="nav-link active" href="borrowing_reports.php">
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
                    <i class="fas fa-chart-bar me-2"></i>Báo cáo mượn sách
                </h1>

                <!-- Filter Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Bộ lọc
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="filter" class="form-label">Thời gian</label>
                                <select class="form-select" id="filter" name="filter" onchange="toggleCustomDates()">
                                    <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                                    <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>>7 ngày qua</option>
                                    <option value="month" <?php echo $filter == 'month' ? 'selected' : ''; ?>>30 ngày qua</option>
                                    <option value="custom" <?php echo $filter == 'custom' ? 'selected' : ''; ?>>Tùy chọn</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="dateFromGroup" style="display: <?php echo $filter == 'custom' ? 'block' : 'none'; ?>;">
                                <label for="date_from" class="form-label">Từ ngày</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3" id="dateToGroup" style="display: <?php echo $filter == 'custom' ? 'block' : 'none'; ?>;">
                                <label for="date_to" class="form-label">Đến ngày</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-2"></i>Lọc
                                </button>
                                <a href="borrowing_reports.php" class="btn btn-secondary">
                                    <i class="fas fa-undo me-2"></i>Làm mới
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['total_borrowings']; ?></h4>
                                        <p class="card-text">Tổng lượt mượn</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-bookmark fa-2x"></i>
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
                                        <h4 class="card-title"><?php echo $stats['active_borrowings']; ?></h4>
                                        <p class="card-text">Đang mượn</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
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
                                        <h4 class="card-title"><?php echo $stats['returned_books']; ?></h4>
                                        <p class="card-text">Đã trả</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['overdue_books']; ?></h4>
                                        <p class="card-text">Quá hạn</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Borrowings List -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Danh sách mượn sách (<?php echo count($borrowings); ?> lượt)
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($borrowings)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Không có dữ liệu mượn sách</h5>
                                        <p class="text-muted">Hãy thử thay đổi bộ lọc để xem dữ liệu khác.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ngày mượn</th>
                                                    <th>Sách</th>
                                                    <th>Người mượn</th>
                                                    <th>Hạn trả</th>
                                                    <th>Trạng thái</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($borrowings as $borrowing): ?>
                                                    <tr>
                                                        <td><?php echo date('d/m/Y', strtotime($borrowing['borrow_date'])); ?></td>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($borrowing['title']); ?></strong>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($borrowing['author']); ?></small>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($borrowing['full_name']); ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($borrowing['email']); ?></small>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($borrowing['return_date'])); ?></td>
                                                        <td>
                                                            <?php
                                                            $return_date = new DateTime($borrowing['return_date']);
                                                            $today = new DateTime();
                                                            $is_overdue = $return_date < $today && $borrowing['status'] == 'borrowed';
                                                            ?>
                                                            <?php if ($borrowing['status'] == 'borrowed'): ?>
                                                                <?php if ($is_overdue): ?>
                                                                    <span class="badge bg-danger">
                                                                        <i class="fas fa-exclamation-triangle me-1"></i>Quá hạn
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning">
                                                                        <i class="fas fa-clock me-1"></i>Đang mượn
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check me-1"></i>Đã trả
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="../book_detail.php?id=<?php echo $borrowing['book_id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" title="Xem sách">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
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

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Popular Books -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-star me-2"></i>Sách mượn nhiều nhất
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($popular_books)): ?>
                                    <p class="text-muted small">Chưa có dữ liệu</p>
                                <?php else: ?>
                                    <?php foreach ($popular_books as $book): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <div class="fw-bold small"><?php echo htmlspecialchars($book['title']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($book['author']); ?></div>
                                            </div>
                                            <span class="badge bg-primary"><?php echo $book['borrow_count']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Active Users -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-friends me-2"></i>Người dùng tích cực
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($active_users)): ?>
                                    <p class="text-muted small">Chưa có dữ liệu</p>
                                <?php else: ?>
                                    <?php foreach ($active_users as $user): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <div class="fw-bold small"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($user['username']); ?></div>
                                            </div>
                                            <span class="badge bg-success"><?php echo $user['borrow_count']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        function toggleCustomDates() {
            const filter = document.getElementById('filter').value;
            const dateFromGroup = document.getElementById('dateFromGroup');
            const dateToGroup = document.getElementById('dateToGroup');
            
            if (filter === 'custom') {
                dateFromGroup.style.display = 'block';
                dateToGroup.style.display = 'block';
            } else {
                dateFromGroup.style.display = 'none';
                dateToGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html> 