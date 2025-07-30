<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/notification.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$notification = new Notification($conn);

// Xử lý đánh dấu đã đọc
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification->markAsRead($_POST['notification_id']);
}

// Lấy thông báo của user
$notifications = $notification->getUserNotifications($_SESSION['user_id'], 50);
$unread_count = $notification->getUnreadCount($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - Hệ thống quản lý thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-bell"></i> Thông báo</h4>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger"><?php echo $unread_count; ?> chưa đọc</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có thông báo nào</h5>
                                <p class="text-muted">Khi có thông báo mới, chúng sẽ xuất hiện ở đây.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="list-group-item <?php echo $notif['is_read'] ? '' : 'list-group-item-primary'; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <?php
                                                    $icon = 'fas fa-info-circle';
                                                    $color = 'text-info';
                                                    switch ($notif['type']) {
                                                        case 'overdue':
                                                            $icon = 'fas fa-exclamation-triangle';
                                                            $color = 'text-warning';
                                                            break;
                                                        case 'return':
                                                            $icon = 'fas fa-check-circle';
                                                            $color = 'text-success';
                                                            break;
                                                        case 'borrow':
                                                            $icon = 'fas fa-book';
                                                            $color = 'text-primary';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?php echo $icon; ?> <?php echo $color; ?> me-2"></i>
                                                    <span class="text-muted small">
                                                        <?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?>
                                                    </span>
                                                    <?php if (!$notif['is_read']): ?>
                                                        <span class="badge bg-primary ms-2">Mới</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            </div>
                                            <?php if (!$notif['is_read']): ?>
                                                <form method="POST" class="ms-2">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                                                    <button type="submit" name="mark_read" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-check"></i> Đã đọc
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html> 