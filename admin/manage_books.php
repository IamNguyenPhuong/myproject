<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Xử lý thêm sách mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $isbn = sanitize($_POST['isbn']);
    $category = sanitize($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $cover_image = uploadImage($_FILES['cover_image'], '../uploads/');
    }
    
    if (addBook($conn, $title, $author, $description, $isbn, $category, $quantity, $cover_image)) {
        $success_message = "Thêm sách thành công!";
    } else {
        $error_message = "Không thể thêm sách. Vui lòng thử lại sau.";
    }
}

// Xử lý xóa sách
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_book'])) {
    $book_id = (int)$_POST['book_id'];
    
    if (deleteBook($conn, $book_id)) {
        $success_message = "Xóa sách thành công!";
    } else {
        $error_message = "Không thể xóa sách. Vui lòng thử lại sau.";
    }
}

// Lấy danh sách sách
$books = getAllBooks($conn);

// Lấy danh sách thể loại
$categories = ['Tiểu thuyết', 'Khoa học', 'Lịch sử', 'Văn học', 'Kinh tế', 'Công nghệ', 'Giáo dục', 'Khác'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sách - Thư viện Online</title>
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
                        <a class="nav-link active" href="manage_books.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>
                        <i class="fas fa-book me-2"></i>Quản lý sách
                    </h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                        <i class="fas fa-plus me-2"></i>Thêm sách mới
                    </button>
                </div>

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

                <!-- Books Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Danh sách sách (<?php echo count($books); ?> cuốn)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($books)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có sách nào</h5>
                                <p class="text-muted">Hãy thêm sách đầu tiên vào thư viện!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Bìa sách</th>
                                            <th>Tên sách</th>
                                            <th>Tác giả</th>
                                            <th>Thể loại</th>
                                            <th>Số lượng</th>
                                            <th>Có sẵn</th>
                                            <th>Ngày thêm</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                            <tr>
                                                <td><?php echo $book['id']; ?></td>
                                                <td>
                                                    <img src="<?php echo $book['cover_image'] ? '../uploads/' . $book['cover_image'] : '../assets/images/default-book.jpg'; ?>" 
                                                         alt="<?php echo htmlspecialchars($book['title']); ?>"
                                                         style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                                    <?php if ($book['isbn']): ?>
                                                        <br><small class="text-muted">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($book['category']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $book['quantity']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $book['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                        <?php echo $book['available_quantity']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($book['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="../book_detail.php?id=<?php echo $book['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="editBook(<?php echo $book['id']; ?>)" title="Chỉnh sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Bạn có chắc muốn xóa sách này?')">
                                                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                            <button type="submit" name="delete_book" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Thêm sách mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Tên sách *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập tên sách.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="author" class="form-label">Tác giả *</label>
                                    <input type="text" class="form-control" id="author" name="author" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập tác giả.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="isbn" class="form-label">ISBN</label>
                                    <input type="text" class="form-control" id="isbn" name="isbn">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Thể loại *</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Chọn thể loại</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Vui lòng chọn thể loại.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Số lượng *</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                            <div class="invalid-feedback">
                                                Vui lòng nhập số lượng.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Bìa sách</label>
                                    <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                                    <div class="form-text">Chọn file ảnh (JPG, PNG, GIF)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div id="imagePreview" class="text-center" style="display: none;">
                                        <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="add_book" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Thêm sách
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Image preview
        document.getElementById('cover_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                document.getElementById('imagePreview').style.display = 'none';
            }
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        function editBook(bookId) {
            // TODO: Implement edit functionality
            alert('Chức năng chỉnh sửa sẽ được phát triển sau!');
        }
    </script>
</body>
</html> 