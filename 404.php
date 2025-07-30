<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Trang không tìm thấy | <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="error-page">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                    <h1 class="display-1 text-muted">404</h1>
                    <h2 class="mb-4">Trang không tìm thấy</h2>
                    <p class="lead text-muted mb-4">
                        Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.
                    </p>
                    <div class="mb-4">
                        <a href="index.php" class="btn btn-primary me-2">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                        <a href="books.php" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>Xem sách
                        </a>
                    </div>
                    <div class="mt-4">
                        <p class="text-muted">
                            <i class="fas fa-search me-2"></i>
                            Bạn có thể tìm kiếm sách trong thư viện của chúng tôi
                        </p>
                        <form action="search.php" method="GET" class="d-inline-block">
                            <div class="input-group" style="max-width: 300px;">
                                <input type="text" name="q" class="form-control" placeholder="Tìm kiếm sách...">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
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