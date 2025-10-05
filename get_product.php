<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    
    $sql = "SELECT * FROM product WHERE product_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
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
        
        $content = "Товар найден:\n";
        $content .= "ID: {$product['product_id']}\n";
        $content .= "Название: {$product['product_name']}\n";
        $content .= "Всего отзывов: {$stats['total_reviews']}\n";
        $content .= "Средний рейтинг: " . round($stats['avg_rating'], 2) . "/5\n\n";
        
        $content .= "Последние отзывы:\n";
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
            $content .= "- {$review['reviewer_name']}: {$stars} - {$review['review_text']}\n";
        }
        
        $reviews_stmt->close();
        $stats_stmt->close();
        
        echo "<!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Информация о товаре</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 20px;
                    background-color: hsl(240, 34%, 86%);
                }
                .container {
                    background: rgb(255, 255, 255);
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .content-box {
                    background:
                    padding: 20px;
                    border-radius: 6px;
                    border-left: 4px solid
                    white-space: pre-line;
                    color:
                    line-height: 1.5;
                }
                .back-link {
                    display: inline-block;
                    padding: 12px 25px;
                    background:
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 10px 5px;
                    transition: background-color 0.3s;
                }
                .back-link:hover {
                    background-color:
                }
                h2 {
                    color:
                    text-align: center;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2> Информация о товаре</h2>
                <div class='content-box'>$content</div>
                <div style='text-align: center;'>
                    <a href='form.html' class='back-link'>Добавить отзыв</a>
                    <a href='view_reviews.php' class='back-link' style='background: #ba68c8;'>Все отзывы</a>
                   </div>
            </div>
        </body>
        </html>";
        
    } else {
        $errorText = "Товар с ID $product_id не найден.";
        
        echo "<!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Товар не найден</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: 100px auto;
                    padding: 20px;
                    background-color: hsl(240, 34%, 86%);
                }
                .container {
                    background: rgb(255, 255, 255);
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .error-box {
                    background:
                    color:
                    padding: 15px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    border-left: 4px solid
                }
                .back-link {
                    display: inline-block;
                    padding: 12px 25px;
                    background:
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 10px;
                    transition: background-color 0.3s;
                }
                .back-link:hover {
                    background-color:
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='error-box'>$errorText</div>
                <div>
                    <a href='form.html' class='back-link'>Добавить отзыв</a>
                    <a href='view_reviews.php' class='back-link'>Все отзывы</a>
      </div>
            </div>
        </body>
        </html>";
    }
    
    $stmt->close();
    $con->close();
} else {
    $errorText = "Укажите product_id в параметрах GET запроса.";
    
    echo "<!DOCTYPE html>
    <html lang='ru'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Ошибка запроса</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 100px auto;
                padding: 20px;
                background-color: hsl(240, 34%, 86%);
            }
            .container {
                background: rgb(255, 255, 255);
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            .error-box {
                background:
                color:
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid
            }
            .back-link {
                display: inline-block;
                padding: 12px 25px;
                background:
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin: 10px;
                transition: background-color 0.3s;
            }
            .back-link:hover {
                background-color:
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='error-box'>$errorText</div>
            <div>
                <a href='form.html' class='back-link'>Добавить отзыв</a>
                <a href='view_reviews.php' class='back-link'>Все отзывы</a>
                 </div>
        </div>
    </body>
    </html>";
}
?>