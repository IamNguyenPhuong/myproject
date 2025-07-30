<?php
// Review and rating system
class BookReview {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Thêm đánh giá mới
    public function addReview($user_id, $book_id, $rating, $comment = '') {
        // Kiểm tra xem user đã đánh giá sách này chưa
        $stmt = $this->conn->prepare("SELECT id FROM book_reviews WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        
        if ($stmt->fetch()) {
            return false; // Đã đánh giá rồi
        }
        
        $stmt = $this->conn->prepare("INSERT INTO book_reviews (user_id, book_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$user_id, $book_id, $rating, $comment])) {
            $this->updateBookRating($book_id);
            return true;
        }
        return false;
    }
    
    // Cập nhật đánh giá
    public function updateReview($review_id, $rating, $comment = '') {
        $stmt = $this->conn->prepare("UPDATE book_reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$rating, $comment, $review_id])) {
            // Lấy book_id để cập nhật rating trung bình
            $stmt = $this->conn->prepare("SELECT book_id FROM book_reviews WHERE id = ?");
            $stmt->execute([$review_id]);
            $book_id = $stmt->fetchColumn();
            $this->updateBookRating($book_id);
            return true;
        }
        return false;
    }
    
    // Xóa đánh giá
    public function deleteReview($review_id) {
        $stmt = $this->conn->prepare("SELECT book_id FROM book_reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $book_id = $stmt->fetchColumn();
        
        $stmt = $this->conn->prepare("DELETE FROM book_reviews WHERE id = ?");
        if ($stmt->execute([$review_id])) {
            $this->updateBookRating($book_id);
            return true;
        }
        return false;
    }
    
    // Lấy đánh giá của sách
    public function getBookReviews($book_id, $limit = 10, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT r.*, u.username, u.full_name 
            FROM book_reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.book_id = ? 
            ORDER BY r.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $book_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Lấy đánh giá của user
    public function getUserReview($user_id, $book_id) {
        $stmt = $this->conn->prepare("SELECT * FROM book_reviews WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        return $stmt->fetch();
    }
    
    // Cập nhật rating trung bình của sách
    private function updateBookRating($book_id) {
        $stmt = $this->conn->prepare("
            UPDATE books SET 
                avg_rating = (
                    SELECT AVG(rating) 
                    FROM book_reviews 
                    WHERE book_id = ?
                ),
                review_count = (
                    SELECT COUNT(*) 
                    FROM book_reviews 
                    WHERE book_id = ?
                )
            WHERE id = ?
        ");
        $stmt->execute([$book_id, $book_id, $book_id]);
    }
    
    // Lấy sách được đánh giá cao nhất
    public function getTopRatedBooks($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT * FROM books 
            WHERE avg_rating > 0 
            ORDER BY avg_rating DESC, review_count DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Kiểm tra user đã mượn sách chưa (để đánh giá)
    public function canUserReview($user_id, $book_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) FROM borrowings 
            WHERE user_id = ? AND book_id = ? AND status = 'returned'
        ");
        $stmt->execute([$user_id, $book_id]);
        return $stmt->fetchColumn() > 0;
    }
}
?> 