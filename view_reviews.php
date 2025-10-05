<?php
include 'config.php';

$sql = "SELECT * FROM product ORDER BY product_id, rating DESC";
$result = $con->query($sql);

$con->close();
?>
<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Все отзывы о товарах</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: hsl(240, 34%, 86%);
        }
        .container {
            background: rgb(255, 255, 255);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .review {
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fafafa;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .review:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #90bbea;
        }
        .product-name {
            font-weight: bold;
            color: #90bbea;
            font-size: 18px;
        }
        .rating {
            color: #FFB74D;
            font-size: 18px;
        }
        .reviewer-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .review-text {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #90bbea;
            color: #555;
            line-height: 1.5;
        }
        .back-link {
            display: inline-block;
            padding: 12px 25px;
            background: #90bbea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #7aa8d8;
        }
        .no-reviews {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }
        .product-id-badge {
            background: #a5d6a7;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        .review-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .reviewer-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📝 Все отзывы о товарах</h1>

        <div id='reviews-container'>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                    $stars = str_repeat('★', $row['rating']);
                    $empty_stars = str_repeat('☆', 5 - $row['rating']);
                    ?>
                    <div class='review'>
                        <div class='review-header'>
                            <span class='product-name'>
                                <?php echo htmlspecialchars($row['product_name']); ?>
                                <span class='product-id-badge'>ID: <?php echo $row['product_id']; ?></span>
                            </span>
                            <span class='rating'><?php echo $stars . $empty_stars; ?> (<?php echo $row['rating']; ?>/5)</span>
                        </div>
                        <div class='reviewer-info'>
                            <span>👤 <?php echo htmlspecialchars($row['reviewer_name']); ?></span>
                            <span>📧 <?php echo htmlspecialchars($row['reviewer_email']); ?></span>
                        </div>
                        <div class='review-text'>
                            <?php echo htmlspecialchars($row['review_text']); ?>
                        </div>
                        <div class='review-meta'>
                            <span>Товар ID: <?php echo $row['product_id']; ?></span>
                            <span>Оценка: <?php echo $row['rating']; ?> из 5</span>
                            <span>Длина отзыва: <?php echo strlen($row['review_text']); ?> символов</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class='no-reviews'>
                    <h3>😔 Отзывов пока нет</h3>
                    <p>Будьте первым, кто оставит отзыв о товаре!</p>
                </div>
            <?php endif; ?>
        </div>

        <div style='text-align: center; margin-top: 30px;'>
            <a href='form.html' class='back-link'>➕ Добавить новый отзыв</a>
            <a href='index.php' class='back-link' style='background: #a5d6a7;'>🏠 На главную</a>
            <a href='#' class='back-link' style='background: #ba68c8;' onclick="scrollToTop()">⬆️ Наверх</a>
        </div>
    </div>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>