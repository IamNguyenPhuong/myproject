<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id);

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Kiểm tra email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    }
    
    // Kiểm tra email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = "Email đã được sử dụng bởi tài khoản khác.";
    }
    
    // Kiểm tra mật khẩu hiện tại nếu muốn đổi mật khẩu
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Mật khẩu hiện tại không đúng.";
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Mật khẩu xác nhận không khớp.";
        }
    }
    
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            if (!empty($new_password)) {
                // Cập nhật thông tin và mật khẩu
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$full_name, $email, $hashed_password, $user_id]);
            } else {
                // Chỉ cập nhật thông tin
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$full_name, $email, $user_id]);
            }
            
            $conn->commit();
            
            // Cập nhật session
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $full_name;
            
            $success_message = "Cập nhật thông tin thành công!";
            
            // Lấy thông tin user mới
            $user = getUserById($conn, $user_id);
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Có lỗi xảy ra. Vui lòng thử lại sau.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Lấy thống kê mượn sách
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_borrowed,
        COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as currently_borrowed,
        COUNT(CASE WHEN status = 'returned' THEN 1 END) as returned,
        COUNT(CASE WHEN return_date < CURDATE() AND status = 'borrowed' THEN 1 END) as overdue
    FROM borrowings 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Thư viện Online</title>
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
                        <a class="nav-link" href="my_books.php">Sách của tôi</a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/">Quản trị</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">Hồ sơ</a></li>
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
            <!-- Profile Information -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>Thông tin cá nhân
                        </h4>
                    </div>
                    <div class="card-body">
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

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    <div class="form-text">Tên đăng nhập không thể thay đổi</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Vai trò</label>
                                    <input type="text" class="form-control" id="role" value="<?php echo $user['role'] == 'admin' ? 'Quản trị viên' : 'Người dùng'; ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Họ và tên *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập họ và tên.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập email hợp lệ.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="created_at" class="form-label">Ngày tham gia</label>
                                    <input type="text" class="form-control" id="created_at" 
                                           value="<?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="updated_at" class="form-label">Cập nhật lần cuối</label>
                                    <input type="text" class="form-control" id="updated_at" 
                                           value="<?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?>" readonly>
                                </div>
                            </div>

                            <hr>

                            <h5 class="mb-3">
                                <i class="fas fa-key me-2"></i>Đổi mật khẩu
                            </h5>
                            <p class="text-muted">Để đổi mật khẩu, vui lòng điền thông tin bên dưới. Nếu không muốn đổi, để trống.</p>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                    <div class="form-text">Tối thiểu 6 ký tự</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật thông tin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics Sidebar -->
            <div class="col-lg-4">
                <!-- User Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê mượn sách
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border-end">
                                    <h4 class="text-primary"><?php echo $stats['total_borrowed']; ?></h4>
                                    <small class="text-muted">Tổng số sách đã mượn</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <h4 class="text-warning"><?php echo $stats['currently_borrowed']; ?></h4>
                                <small class="text-muted">Đang mượn</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?php echo $stats['returned']; ?></h4>
                                <small class="text-muted">Đã trả</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger"><?php echo $stats['overdue']; ?></h4>
                                <small class="text-muted">Quá hạn</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="my_books.php" class="btn btn-outline-primary">
                                <i class="fas fa-bookmark me-2"></i>Xem sách đã mượn
                            </a>
                            <a href="books.php" class="btn btn-outline-success">
                                <i class="fas fa-search me-2"></i>Tìm sách mới
                            </a>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <a href="admin/" class="btn btn-outline-warning">
                                    <i class="fas fa-cog me-2"></i>Quản trị hệ thống
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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