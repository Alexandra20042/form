<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    $sql = "SELECT * FROM product WHERE product_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Получаем статистику по товару
        $stats_sql = "SELECT 
            COUNT(*) as total_reviews,
            AVG(CAST(rating AS UNSIGNED)) as avg_rating
            FROM product 
            WHERE product_id = ?";
        $stats_stmt = $con->prepare($stats_sql);
        $stats_stmt->bind_param("i", $product_id);
        $stats_stmt->execute();
        $stats_result = $stats_stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        
        echo "Товар найден:\n";
        echo "ID: {$product['product_id']}\n";
        echo "Название: {$product['product_name']}\n";
        echo "Всего отзывов: {$stats['total_reviews']}\n";
        echo "Средний рейтинг: " . round($stats['avg_rating'], 2) . "/5\n\n";
        
        echo "Последние отзывы:\n";
        $reviews_sql = "SELECT reviewer_name, rating, review_text 
                       FROM product 
                       WHERE product_id = ? 
                       ORDER BY product_id DESC 
                       LIMIT 5";
        $reviews_stmt = $con->prepare($reviews_sql);
        $reviews_stmt->bind_param("i", $product_id);
        $reviews_stmt->execute();
        $reviews_result = $reviews_stmt->get_result();
        
        while ($review = $reviews_result->fetch_assoc()) {
            $stars = str_repeat('★', $review['rating']);
            echo "- {$review['reviewer_name']}: {$stars} - {$review['review_text']}\n";
        }
        
        $reviews_stmt->close();
        $stats_stmt->close();
    } else {
        echo "Товар с ID $product_id не найден.";
    }
    
    $stmt->close();
    $con->close();
} else {
    echo "Укажите product_id в параметрах GET запроса.";
}
?>