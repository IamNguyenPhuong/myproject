# Library Management System

Hệ thống quản lý thư viện trực tuyến được xây dựng bằng PHP thuần, MySQL và Bootstrap 5.

## 🌟 Tính năng chính

### 👥 Quản lý người dùng
- Đăng ký và đăng nhập tài khoản
- Phân quyền admin/user
- Quản lý hồ sơ cá nhân
- Bảo mật mật khẩu với hashing

### 📚 Quản lý sách
- Thêm, sửa, xóa sách
- Upload hình ảnh bìa sách
- Tìm kiếm và lọc sách
- Phân loại sách theo danh mục
- Hệ thống đánh giá và bình luận

### 📖 Mượn trả sách
- Mượn và trả sách
- Theo dõi lịch sử mượn trả
- Thông báo sách quá hạn
- Giới hạn số sách mượn

### 🔔 Hệ thống thông báo
- Thông báo trong ứng dụng
- Thông báo sách quá hạn
- Đánh dấu đã đọc/chưa đọc
- Quản lý thông báo

### ⭐ Hệ thống đánh giá
- Đánh giá sách từ 1-5 sao
- Viết bình luận về sách
- Xem sách được đánh giá cao nhất
- Thống kê đánh giá

### 📊 Báo cáo và thống kê
- Thống kê sách mượn nhiều nhất
- Báo cáo người dùng tích cực
- Thống kê theo thời gian
- Xuất báo cáo

## 🛠️ Công nghệ sử dụng

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 5.1.3
- **Icons:** Font Awesome 6.0.0
- **Database Access:** PDO

## 📋 Yêu cầu hệ thống

- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Apache/Nginx web server
- Extensions PHP: PDO, PDO_MySQL, GD, mbstring

## 🚀 Cài đặt

### 1. Clone repository
```bash
git clone <repository-url>
cd library-management-system
```

### 2. Cấu hình database
- Tạo database MySQL mới
- Import file `database/library_system.sql`
- Cập nhật thông tin database trong `config/database.php`

### 3. Cấu hình ứng dụng
- Cập nhật `config/config.php` với thông tin phù hợp
- Tạo thư mục `uploads/` và `logs/` với quyền 755
- Cấu hình web server để trỏ đến thư mục gốc

### 4. Tạo admin account
```bash
php create_admin.php
```

### 5. Truy cập ứng dụng
- Mở trình duyệt và truy cập: `http://localhost/library-management-system`
- Đăng nhập với tài khoản admin đã tạo

## 🔧 Cấu hình cho Production

### 1. Bảo mật
- Thay đổi thông tin database mặc định
- Bật HTTPS/SSL
- Cấu hình firewall
- Bật error logging (không hiển thị lỗi)

### 2. Performance
- Bật Gzip compression
- Cấu hình browser caching
- Tối ưu hóa database queries
- Sử dụng CDN cho static files

### 3. Monitoring
- Cấu hình error logging
- Set up backup automation
- Monitor server resources
- Track user activity

## 📁 Cấu trúc thư mục

```
library-management-system/
├── admin/                 # Admin panel
├── assets/               # Static files (CSS, JS, images)
├── config/               # Configuration files
├── database/             # Database schema
├── includes/             # PHP includes and functions
├── uploads/              # Uploaded files
├── logs/                 # Application logs
├── index.php            # Homepage
├── login.php            # Login page
├── register.php         # Registration page
├── books.php            # Books listing
├── book_detail.php      # Book details
├── search.php           # Search functionality
├── notifications.php    # Notifications
├── top_rated_books.php  # Top rated books
├── .htaccess            # Apache configuration
└── README.md            # This file
```

## 🔐 Bảo mật

### Tính năng bảo mật đã triển khai:
- **SQL Injection Protection:** Sử dụng PDO prepared statements
- **XSS Protection:** Sanitize tất cả user input
- **CSRF Protection:** CSRF tokens trên tất cả forms
- **Session Security:** Secure session configuration
- **Password Hashing:** bcrypt password hashing
- **Rate Limiting:** Giới hạn số request
- **File Upload Security:** Validate file types và size
- **Directory Protection:** .htaccess protection

### Bảo mật cho Production:
- Sử dụng environment variables cho sensitive data
- Enable HTTPS/SSL
- Configure proper file permissions
- Regular security updates
- Monitor error logs

## 📊 Database Schema

### Bảng chính:
- `users` - Thông tin người dùng
- `books` - Thông tin sách
- `categories` - Danh mục sách
- `borrowings` - Lịch sử mượn trả
- `notifications` - Thông báo
- `book_reviews` - Đánh giá sách
- `activity_logs` - Log hoạt động
- `rate_limits` - Rate limiting
- `settings` - Cấu hình hệ thống

## 🧪 Testing

### Manual Testing:
- Test tất cả chức năng CRUD
- Test user authentication
- Test book borrowing/returning
- Test search functionality
- Test admin panel
- Test notification system
- Test review system

### Security Testing:
- Test SQL injection
- Test XSS attacks
- Test CSRF protection
- Test file upload security
- Test session security

## 🔄 Deployment

### Shared Hosting:
1. Upload tất cả files lên hosting
2. Import database schema
3. Cập nhật database configuration
4. Test tất cả chức năng

### VPS/Dedicated Server:
1. Cài đặt LAMP stack
2. Upload application files
3. Import database
4. Cấu hình web server
5. Set up SSL certificate
6. Configure monitoring

### Cloud Platforms:
- **Heroku:** Sử dụng Heroku CLI
- **AWS:** Sử dụng EC2 hoặc Elastic Beanstalk
- **Google Cloud:** Sử dụng App Engine
- **Azure:** Sử dụng App Service

## 📝 Changelog

### Version 1.0.0
- ✅ Hệ thống quản lý sách cơ bản
- ✅ Quản lý người dùng và phân quyền
- ✅ Hệ thống mượn trả sách
- ✅ Tìm kiếm và lọc sách
- ✅ Admin panel
- ✅ Hệ thống thông báo
- ✅ Hệ thống đánh giá và bình luận
- ✅ Bảo mật nâng cao
- ✅ Responsive design
- ✅ Error handling
- ✅ Logging system

## 🤝 Đóng góp

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📄 License

Dự án này được phân phối dưới giấy phép MIT. Xem file `LICENSE` để biết thêm chi tiết.

## 📞 Hỗ trợ

Nếu bạn gặp vấn đề hoặc có câu hỏi:
- Tạo issue trên GitHub
- Liên hệ: info@thuvienonline.com
- Documentation: [Wiki](link-to-wiki)

## 🙏 Acknowledgments

- Bootstrap team cho UI framework
- Font Awesome cho icons
- MySQL team cho database
- PHP community cho ngôn ngữ lập trình

---

**Lưu ý:** Đây là phiên bản production-ready với đầy đủ tính năng bảo mật và tối ưu hóa. Hãy đọc kỹ deployment checklist trước khi triển khai lên production. 