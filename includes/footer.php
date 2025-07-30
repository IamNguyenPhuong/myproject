<?php
require_once __DIR__ . '/../config/config.php';
?>
    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-book-open me-2"></i><?php echo APP_NAME; ?></h5>
                    <p class="mb-0">Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay.</p>
                    <div class="mt-3">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>Liên kết nhanh</h6>
                    <ul class="list-unstyled">
                        <li><a href="books.php" class="text-light text-decoration-none">Tất cả sách</a></li>
                        <li><a href="top_rated_books.php" class="text-light text-decoration-none">Sách hay nhất</a></li>
                        <li><a href="search.php" class="text-light text-decoration-none">Tìm kiếm</a></li>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="login.php" class="text-light text-decoration-none">Đăng nhập</a></li>
                            <li><a href="register.php" class="text-light text-decoration-none">Đăng ký</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Liên hệ</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Quận XYZ, TP.HCM</li>
                        <li><i class="fas fa-phone me-2"></i>(+84) 123 456 789</li>
                        <li><i class="fas fa-envelope me-2"></i>info@thuvienonline.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Tất cả quyền được bảo lưu.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="privacy.php" class="text-light text-decoration-none me-3">Chính sách bảo mật</a>
                        <a href="terms.php" class="text-light text-decoration-none">Điều khoản sử dụng</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <!-- CSRF Token for AJAX requests -->
    <script>
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</body>
</html> 