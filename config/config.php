<?php
// Application Configuration
// Set environment (development/production)
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// Base URL - Update this for production
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/myproject');

// Application settings
define('APP_NAME', 'Thư viện Online');
define('APP_VERSION', '1.0.0');

// Security settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', 'uploads/');

// Pagination settings
define('ITEMS_PER_PAGE', 12);
define('MAX_PAGES_DISPLAY', 5);

// Error reporting
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}
?> 