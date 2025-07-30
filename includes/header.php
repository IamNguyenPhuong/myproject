<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/notification.php';

$notification = new Notification($conn);
$unread_count = 0;
if (isLoggedIn()) {
    $unread_count = $notification->getUnreadCount($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo APP_NAME; ?> - Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay">
    <meta name="keywords" content="thư viện, sách, đọc sách, mượn sách, quản lý sách">
    <meta name="author" content="<?php echo APP_NAME; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>">
    <meta property="og:title" content="<?php echo APP_NAME; ?>">
    <meta property="og:description" content="Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo BASE_URL; ?>">
    <meta property="twitter:title" content="<?php echo APP_NAME; ?>">
    <meta property="twitter:description" content="Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open me-2"></i><?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books.php">
                            <i class="fas fa-book me-1"></i>Sách
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="top_rated_books.php">
                            <i class="fas fa-star me-1"></i>Sách hay
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_books.php">
                                <i class="fas fa-user me-1"></i>Sách của tôi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link notification-badge" href="notifications.php">
                                <i class="fas fa-bell"></i> Thông báo
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i>Quản trị
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="admin/books.php">Quản lý sách</a></li>
                                    <li><a class="dropdown-item" href="admin/users.php">Quản lý người dùng</a></li>
                                    <li><a class="dropdown-item" href="admin/borrowings.php">Quản lý mượn trả</a></li>
                                    <li><a class="dropdown-item" href="admin/reports.php">Báo cáo</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <!-- Search Form -->
                <form class="d-flex me-3" action="search.php" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Tìm kiếm sách..." aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Hồ sơ</a></li>
                                <li><a class="dropdown-item" href="my_books.php">Sách của tôi</a></li>
                                <li><a class="dropdown-item" href="notifications.php">Thông báo</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav> 