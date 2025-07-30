<?php
// Notification system
class Notification {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Tạo thông báo mới
    public function createNotification($user_id, $type, $message, $related_id = null) {
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, type, message, related_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$user_id, $type, $message, $related_id]);
    }
    
    // Lấy thông báo của user
    public function getUserNotifications($user_id, $limit = 10) {
        $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Đánh dấu thông báo đã đọc
    public function markAsRead($notification_id) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notification_id]);
    }
    
    // Đếm thông báo chưa đọc
    public function getUnreadCount($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }
    
    // Xóa thông báo cũ (30 ngày)
    public function cleanOldNotifications() {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        return $stmt->execute();
    }
    
    // Thông báo sách sắp hết hạn
    public function notifyOverdueBooks() {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.id as user_id, u.username, bk.title 
            FROM borrowings b 
            JOIN users u ON b.user_id = u.id 
            JOIN books bk ON b.book_id = bk.id 
            WHERE b.status = 'borrowed' 
            AND b.return_date < NOW() 
            AND b.notified = 0
        ");
        $stmt->execute();
        $overdue_books = $stmt->fetchAll();
        
        foreach ($overdue_books as $book) {
            $this->createNotification(
                $book['user_id'],
                'overdue',
                "Sách '{$book['title']}' đã quá hạn trả. Vui lòng trả sách sớm!",
                $book['id']
            );
            
            // Đánh dấu đã thông báo
            $update_stmt = $this->conn->prepare("UPDATE borrowings SET notified = 1 WHERE id = ?");
            $update_stmt->execute([$book['id']]);
        }
        
        return count($overdue_books);
    }
}
?> 