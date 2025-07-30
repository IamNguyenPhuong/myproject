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
    <title>500 - Lỗi máy chủ | <?php echo APP_NAME; ?></title>
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
                    <i class="fas fa-exclamation-circle fa-5x text-danger mb-4"></i>
                    <h1 class="display-1 text-muted">500</h1>
                    <h2 class="mb-4">Lỗi máy chủ</h2>
                    <p class="lead text-muted mb-4">
                        Xin lỗi, đã xảy ra lỗi nội bộ. Chúng tôi đang khắc phục vấn đề này.
                    </p>
                    <div class="mb-4">
                        <a href="index.php" class="btn btn-primary me-2">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                        <button onclick="location.reload()" class="btn btn-outline-primary">
                            <i class="fas fa-redo me-2"></i>Thử lại
                        </button>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-clock me-2"></i>
                        Vui lòng thử lại sau vài phút. Nếu vấn đề vẫn tiếp tục, hãy liên hệ với chúng tôi.
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