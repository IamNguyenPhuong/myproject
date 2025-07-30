# Production Deployment Guide - Library Management System

## 🚀 Tổng quan

Hướng dẫn chi tiết để deploy hệ thống quản lý thư viện lên production server với đầy đủ tính năng bảo mật và tối ưu hóa.

## 📋 Checklist trước khi deploy

### ✅ Đã hoàn thành:
- [x] Cập nhật database schema với bảng mới
- [x] Thêm tính năng bảo mật (CSRF, Rate limiting, Activity logging)
- [x] Tối ưu hóa cấu hình cho production
- [x] Tạo error pages (404, 403, 500)
- [x] Cấu hình .htaccess cho bảo mật
- [x] Cập nhật header/footer với SEO meta tags
- [x] Thêm CSS cho accessibility và performance
- [x] Tạo deployment checklist

## 🎯 Lựa chọn hosting

### 1. Shared Hosting (Khuyến nghị cho người mới)
**Ưu điểm:** Dễ sử dụng, giá rẻ, hỗ trợ tốt
**Nhược điểm:** Ít kiểm soát, giới hạn tài nguyên

**Nhà cung cấp tốt:**
- **Hostinger** - $2.99/tháng
- **Namecheap** - $2.88/tháng
- **GoDaddy** - $5.99/tháng

### 2. VPS (Khuyến nghị cho dự án lớn)
**Ưu điểm:** Kiểm soát hoàn toàn, hiệu suất cao
**Nhược điểm:** Cần kiến thức server management

**Nhà cung cấp tốt:**
- **DigitalOcean** - $5/tháng
- **Vultr** - $2.5/tháng
- **Linode** - $5/tháng

### 3. Cloud Platforms
**Ưu điểm:** Scalable, managed services
**Nhược điểm:** Phức tạp, giá cao hơn

**Lựa chọn:**
- **Heroku** - Dễ deploy
- **AWS** - Mạnh mẽ nhất
- **Google Cloud** - Tích hợp tốt

## 🔧 Cấu hình cho Production

### 1. Environment Variables
Tạo file `.env` (hoặc cấu hình trong hosting panel):

```env
# Database
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_secure_password

# Application
APP_ENV=production
BASE_URL=https://yourdomain.com
```

### 2. Database Security
```sql
-- Tạo user database riêng (không dùng root)
CREATE USER 'library_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON library_system.* TO 'library_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. File Permissions
```bash
# Directories
chmod 755 uploads/
chmod 755 logs/
chmod 755 config/

# Files
chmod 644 *.php
chmod 644 assets/css/*
chmod 644 assets/js/*
```

## 📁 Cấu trúc files cần upload

```
public_html/
├── admin/                 # Admin panel
├── assets/               # CSS, JS, images
├── config/               # Configuration (protected)
├── includes/             # PHP includes (protected)
├── uploads/              # Uploaded files
├── logs/                 # Application logs (protected)
├── index.php            # Homepage
├── login.php            # Login
├── register.php         # Registration
├── books.php            # Books listing
├── book_detail.php      # Book details
├── search.php           # Search
├── notifications.php    # Notifications
├── top_rated_books.php  # Top rated books
├── 404.php             # Error pages
├── 403.php
├── 500.php
├── .htaccess            # Apache config
└── README.md
```

## 🚀 Bước deploy chi tiết

### Bước 1: Chuẩn bị hosting
1. **Đăng ký hosting** và mua domain
2. **Tạo database** MySQL
3. **Cấu hình PHP** (version 7.4+)
4. **Bật SSL certificate**

### Bước 2: Upload files
1. **Upload tất cả files** lên thư mục public_html
2. **Tạo thư mục** uploads/ và logs/ với quyền 755
3. **Kiểm tra** tất cả files đã upload đầy đủ

### Bước 3: Cấu hình database
1. **Import database schema:**
   ```sql
   -- Import file database/library_system.sql
   ```

2. **Cập nhật config/database.php:**
   ```php
   $host = 'your_database_host';
   $dbname = 'your_database_name';
   $username = 'your_database_user';
   $password = 'your_database_password';
   ```

### Bước 4: Cấu hình ứng dụng
1. **Cập nhật config/config.php:**
   ```php
   define('ENVIRONMENT', 'production');
   define('BASE_URL', 'https://yourdomain.com');
   ```

2. **Kiểm tra .htaccess** đã được upload

### Bước 5: Tạo admin account
1. **Tạo file tạm** create_admin.php:
   ```php
   <?php
   require_once 'config/database.php';
   
   $admin_username = 'admin';
   $admin_email = 'admin@yourdomain.com';
   $admin_password = 'SecurePassword123!';
   $admin_full_name = 'Administrator';
   
   $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
   $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, created_at) VALUES (?, ?, ?, ?, 'admin', NOW())");
   $stmt->execute([$admin_username, $admin_email, $hashed_password, $admin_full_name]);
   
   echo "Admin created successfully!";
   ?>
   ```

2. **Chạy script** và **xóa file** sau khi hoàn thành

### Bước 6: Testing
1. **Test homepage** - http://yourdomain.com
2. **Test admin login** với tài khoản vừa tạo
3. **Test tất cả chức năng** chính
4. **Kiểm tra error pages** (404, 403, 500)

## 🔒 Bảo mật Production

### 1. SSL/HTTPS
```apache
# .htaccess - Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 2. Security Headers
```apache
# .htaccess - Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### 3. File Protection
```apache
# .htaccess - Protect sensitive directories
<Directory "config">
    Order allow,deny
    Deny from all
</Directory>
```

### 4. Error Handling
```php
// config/config.php
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'logs/error.log');
}
```

## 📊 Monitoring và Maintenance

### 1. Error Logging
- **Kiểm tra logs/error.log** hàng ngày
- **Set up email alerts** cho critical errors
- **Monitor error rates** và trends

### 2. Performance Monitoring
- **Monitor page load times**
- **Track database performance**
- **Monitor server resources** (CPU, RAM, disk)

### 3. Backup Strategy
```bash
# Database backup (daily)
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Files backup (weekly)
tar -czf files_backup_$(date +%Y%m%d).tar.gz public_html/
```

### 4. Regular Maintenance
- **Update PHP version** khi có bản mới
- **Update dependencies** (Bootstrap, Font Awesome)
- **Clean old logs** và temporary files
- **Optimize database** tables

## 🐛 Troubleshooting

### Lỗi thường gặp:

#### 1. Database Connection Error
```php
// Kiểm tra thông tin database
// Kiểm tra database user permissions
// Kiểm tra database host accessibility
```

#### 2. 500 Internal Server Error
```bash
# Kiểm tra error logs
tail -f logs/error.log

# Kiểm tra file permissions
ls -la config/
ls -la includes/
```

#### 3. Upload không hoạt động
```bash
# Kiểm tra thư mục uploads/
chmod 755 uploads/
chown www-data:www-data uploads/
```

#### 4. .htaccess không hoạt động
```apache
# Kiểm tra Apache mod_rewrite
# Kiểm tra AllowOverride All
# Kiểm tra file .htaccess syntax
```

## 📈 Performance Optimization

### 1. Caching
```apache
# .htaccess - Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
</IfModule>
```

### 2. Compression
```apache
# .htaccess - Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
```

### 3. Database Optimization
```sql
-- Optimize tables regularly
OPTIMIZE TABLE books, users, borrowings;

-- Add indexes for slow queries
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_borrowings_user_date ON borrowings(user_id, borrow_date);
```

## 🔄 Update và Maintenance

### 1. Update Process
1. **Backup** database và files
2. **Test** trên staging environment
3. **Upload** new files
4. **Run** database migrations
5. **Test** production environment
6. **Monitor** for errors

### 2. Version Control
```bash
# Git workflow
git checkout -b hotfix/critical-fix
# Make changes
git commit -m "Fix critical security issue"
git push origin hotfix/critical-fix
# Deploy to production
```

## 📞 Support và Documentation

### 1. Documentation
- **User Manual** - Hướng dẫn sử dụng
- **Admin Guide** - Hướng dẫn quản trị
- **API Documentation** - Nếu có API
- **Troubleshooting Guide** - Xử lý sự cố

### 2. Support Channels
- **Email Support** - support@yourdomain.com
- **Phone Support** - (+84) 123 456 789
- **Live Chat** - Trên website
- **Knowledge Base** - FAQ và tutorials

## 🎯 Kết luận

Sau khi hoàn thành tất cả các bước trên, website của bạn sẽ:
- ✅ **Bảo mật cao** với HTTPS, CSRF protection, rate limiting
- ✅ **Performance tốt** với caching, compression, optimization
- ✅ **Monitoring đầy đủ** với error logging, performance tracking
- ✅ **Maintenance dễ dàng** với backup strategy, update process
- ✅ **User experience tốt** với responsive design, accessibility

**Lưu ý quan trọng:** Luôn test kỹ trước khi deploy lên production và có plan backup/rollback sẵn sàng.

---

**Chúc bạn deploy thành công! 🚀** 