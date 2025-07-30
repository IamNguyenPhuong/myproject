<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Xử lý thay đổi vai trò người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    
    if ($new_role == 'admin' || $new_role == 'user') {
        $stmt = $conn->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ? AND id != ?");
        if ($stmt->execute([$new_role, $user_id, $_SESSION['user_id']])) {
            $success_message = "Thay đổi vai trò thành công!";
        } else {
            $error_message = "Không thể thay đổi vai trò. Vui lòng thử lại sau.";
        }
    }
}

// Xử lý xóa người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $success_message = "Xóa người dùng thành công!";
        } else {
            $error_message = "Không thể xóa người dùng. Vui lòng thử lại sau.";
        }
    } else {
        $error_message = "Không thể xóa tài khoản của chính mình!";
    }
}

// Lấy danh sách người dùng
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(br.id) as total_borrowings,
           COUNT(CASE WHEN br.status = 'borrowed' THEN 1 END) as active_borrowings,
           COUNT(CASE WHEN br.return_date < CURDATE() AND br.status = 'borrowed' THEN 1 END) as overdue_books
    FROM users u
    LEFT JOIN borrowings br ON u.id = br.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Thư viện Online</title>
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
                        <a class="nav-link active" href="manage_users.php">
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
                    <i class="fas fa-users me-2"></i>Quản lý người dùng
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

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Danh sách người dùng (<?php echo count($users); ?> người)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có người dùng nào</h5>
                                <p class="text-muted">Người dùng sẽ xuất hiện ở đây khi đăng ký!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên đăng nhập</th>
                                            <th>Họ tên</th>
                                            <th>Email</th>
                                            <th>Vai trò</th>
                                            <th>Thống kê mượn sách</th>
                                            <th>Ngày tham gia</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-primary ms-1">Bạn</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-primary"><?php echo $user['role'] == 'admin' ? 'Quản trị viên' : 'Người dùng'; ?></span>
                                                    <?php else: ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <select name="new_role" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Người dùng</option>
                                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                                            </select>
                                                            <input type="hidden" name="change_role" value="1">
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <div>Tổng: <span class="badge bg-info"><?php echo $user['total_borrowings']; ?></span></div>
                                                        <div>Đang mượn: <span class="badge bg-warning"><?php echo $user['active_borrowings']; ?></span></div>
                                                        <?php if ($user['overdue_books'] > 0): ?>
                                                            <div>Quá hạn: <span class="badge bg-danger"><?php echo $user['overdue_books']; ?></span></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="viewUserDetails(<?php echo $user['id']; ?>)" title="Xem chi tiết">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <form method="POST" class="d-inline" 
                                                                  onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h5 class="card-title"><?php echo count(array_filter($users, function($u) { return $u['role'] == 'user'; })); ?></h5>
                                <p class="card-text">Người dùng thường</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                                <h5 class="card-title"><?php echo count(array_filter($users, function($u) { return $u['role'] == 'admin'; })); ?></h5>
                                <p class="card-text">Quản trị viên</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-bookmark fa-2x text-success mb-2"></i>
                                <h5 class="card-title"><?php echo array_sum(array_column($users, 'active_borrowings')); ?></h5>
                                <p class="card-text">Đang mượn sách</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h5 class="card-title"><?php echo array_sum(array_column($users, 'overdue_books')); ?></h5>
                                <p class="card-text">Sách quá hạn</p>
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
        function viewUserDetails(userId) {
            // TODO: Implement user details view
            alert('Chức năng xem chi tiết người dùng sẽ được phát triển sau!');
        }
    </script>
</body>
</html> 