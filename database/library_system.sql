-- Tạo database
CREATE DATABASE IF NOT EXISTS library_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_system;

-- Bảng users (người dùng)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng books (sách)
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    isbn VARCHAR(20) UNIQUE,
    category VARCHAR(50),
    quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng borrowings (mượn sách)
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date DATE NOT NULL,
    actual_return_date TIMESTAMP NULL,
    status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Tạo admin user mặc định
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Thêm một số sách mẫu
INSERT INTO books (title, author, description, isbn, category, quantity, available_quantity) VALUES 
('Đắc Nhân Tâm', 'Dale Carnegie', 'Cuốn sách về nghệ thuật đối nhân xử thế và thu phục lòng người.', '978-604-1-00001-1', 'Kỹ năng sống', 5, 5),
('Nhà Giả Kim', 'Paulo Coelho', 'Câu chuyện về hành trình tìm kiếm kho báu và ý nghĩa cuộc sống.', '978-604-1-00002-2', 'Tiểu thuyết', 3, 3),
('Tuổi Trẻ Đáng Giá Bao Nhiêu', 'Rosie Nguyễn', 'Những trải nghiệm và bài học quý giá từ hành trình du lịch.', '978-604-1-00003-3', 'Du lịch', 4, 4),
('Cách Nghĩ Để Thành Công', 'Napoleon Hill', 'Những nguyên tắc thành công được đúc kết từ nghiên cứu về những người thành đạt.', '978-604-1-00004-4', 'Kinh doanh', 6, 6),
('Sapiens: Lược Sử Loài Người', 'Yuval Noah Harari', 'Khám phá lịch sử phát triển của loài người từ thời nguyên thủy đến hiện đại.', '978-604-1-00005-5', 'Lịch sử', 2, 2),
('Đọc Vị Bất Kỳ Ai', 'David J. Lieberman', 'Nghệ thuật thấu hiểu tâm lý con người thông qua ngôn ngữ cơ thể.', '978-604-1-00006-6', 'Tâm lý học', 4, 4);

-- Tạo indexes để tối ưu hiệu suất
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_books_category ON books(category);
CREATE INDEX idx_borrowings_user_id ON borrowings(user_id);
CREATE INDEX idx_borrowings_book_id ON borrowings(book_id);
CREATE INDEX idx_borrowings_status ON borrowings(status);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email); 

-- Thêm bảng activity_logs cho security logging
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Thêm bảng rate_limits cho rate limiting
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip_address, action),
    INDEX idx_created_at (created_at)
);

-- Thêm bảng settings cho application configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Thư viện Online', 'Tên website'),
('site_description', 'Khám phá kho tàng tri thức với hàng nghìn cuốn sách hay', 'Mô tả website'),
('max_books_per_user', '5', 'Số sách tối đa mỗi user có thể mượn'),
('loan_duration_days', '14', 'Thời hạn mượn sách (ngày)'),
('enable_registration', '1', 'Cho phép đăng ký tài khoản mới'),
('maintenance_mode', '0', 'Chế độ bảo trì');

-- Thêm cột status vào bảng books
ALTER TABLE books ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'deleted') DEFAULT 'active';

-- Thêm cột last_login vào bảng users
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL;

-- Thêm cột login_attempts vào bảng users
ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0;

-- Thêm cột locked_until vào bảng users
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL;

-- Tạo index cho performance
CREATE INDEX IF NOT EXISTS idx_books_status ON books(status);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_borrowings_status ON borrowings(status); 