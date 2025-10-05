<?php
require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $product_id = trim($_POST['product_id'] ?? '');
    $product_name = trim($_POST['product_name'] ?? '');
    $reviewer_name = trim($_POST['reviewer_name'] ?? '');
    $reviewer_email = trim($_POST['reviewer_email'] ?? '');
    $review_text = trim($_POST['review_text'] ?? '');
    $rating = trim($_POST['rating'] ?? '');

    if (empty($product_id)) {
        $errors[] = "Поле 'ID товара' обязательно для заполнения.";
    } elseif (!is_numeric($product_id) || $product_id < 1) {
        $errors[] = "ID товара должен быть положительным числом.";
    }
    if (empty($product_name)) {
        $errors[] = "Поле 'Название товара' обязательно для заполнения.";
    } elseif (strlen($product_name) > 105) {
        $errors[] = "Название товара не должно превышать 105 символов.";
    }
    if (empty($reviewer_name)) {
        $errors[] = "Поле 'Ваше имя' обязательно для заполнения.";
    } elseif (strlen($reviewer_name) > 25) {
        $errors[] = "Имя не должно превышать 25 символов.";
    } elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]+$/u', $reviewer_name)) {
        $errors[] = "Имя может содержать только буквы и пробелы.";
    }
    if (empty($reviewer_email)) {
        $errors[] = "Поле 'Email' обязательно для заполнения.";
    } elseif (!filter_var($reviewer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email адрес.";
    } elseif (strlen($reviewer_email) > 50) {
        $errors[] = "Email не должен превышать 50 символов.";
    }
    if (empty($review_text)) {
        $errors[] = "Поле 'Текст отзыва' обязательно для заполнения.";
    } elseif (strlen($review_text) > 100) {
        $errors[] = "Текст отзыва не должен превышать 100 символов.";
    }
    if (empty($rating)) {
        $errors[] = "Поле 'Оценка' обязательно для заполнения.";
    } elseif (!in_array($rating, ['1', '2', '3', '4', '5'])) {
        $errors[] = "Выберите корректную оценку от 1 до 5.";
    }

    echo "<!DOCTYPE html>
    <html lang='ru'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Результат обработки формы</title>
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
            .error-box {
                background:
                color:
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid
                white-space: pre-line;
            }
            .success-box {
                background:
                color:
                padding: 20px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid
                white-space: pre-line;
            }
            .info-box {
                background:
                color:
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid
                white-space: pre-line;
            }
            .back-link {
                display: inline-block;
                padding: 12px 25px;
                background:
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin: 5px;
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
            <h2>📋 Результат обработки формы</h2>";

    if (!empty($errors)) {
        $errorText = "Ошибки валидации:\n";
        foreach ($errors as $error) {
            $errorText .= "- $error\n";
        }
        echo "<div class='error-box'>$errorText</div>
              <div style='text-align: center;'>
                  <a href='form.html' class='back-link'>Вернуться к форме</a>
                </div>";
    } else {
        try {
            if ($con->connect_error) {
                throw new Exception("Ошибка подключения к базе данных: " . $con->connect_error);
            }
            
            $con->set_charset("utf8");

            $check_sql = "SELECT product_name FROM product WHERE product_id = ? LIMIT 1";
            $check_stmt = $con->prepare($check_sql);
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $existing_product = $check_result->fetch_assoc();
                $existing_name = $existing_product['product_name'];
                
                if ($existing_name !== $product_name) {
                    $errorText = "Ошибка: Товар с ID $product_id уже существует!\n";
                    $errorText .= "Существующий товар: $existing_name\n";
                    $errorText .= "Введенный товар: $product_name\n";
                    $errorText .= "Пожалуйста, используйте правильное название товара или измените ID.";
                    
                    echo "<div class='error-box'>$errorText</div>
                          <div style='text-align: center;'>
                              <a href='form.html' class='back-link'>Вернуться к форме</a>
                              <a href='get_product.php?product_id=$product_id' class='back-link' style='background: #a5d6a7;'>Посмотреть товар</a>
                          </div>";
                    $check_stmt->close();
                    exit;
                }
            }
            $check_stmt->close();

            $sql = "INSERT INTO product (product_id, product_name, reviewer_name, reviewer_email, review_text, rating) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $con->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Ошибка подготовки запроса: " . $con->error);
            }
            
            $stmt->bind_param("isssss", $product_id, $product_name, $reviewer_name, $reviewer_email, $review_text, $rating);
            
            if ($stmt->execute()) {
                
                $successText = "Данные успешно сохранены!\n";
                $successText .= "ID товара: $product_id\n";
                $successText .= "Название товара: $product_name\n";
                $successText .= "Имя рецензента: $reviewer_name\n";
                $successText .= "Email: $reviewer_email\n";
                $successText .= "Текст отзыва: $review_text\n";
                $successText .= "Оценка: $rating/5";
                
                echo "<div class='success-box'>$successText</div>";

                $stats_sql = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(CAST(rating AS UNSIGNED)) as avg_rating
                    FROM product 
                    WHERE product_id = ?";
                $stats_stmt = $con->prepare($stats_sql);
                $stats_stmt->bind_param("i", $product_id);
                $stats_stmt->execute();
                $stats_result = $stats_stmt->get_result();
                
                if ($stats_row = $stats_result->fetch_assoc()) {
                    $statsText = "\n\nСтатистика по товару:\n";
                    $statsText .= "Всего отзывов: {$stats_row['total_reviews']}\n";
                    $statsText .= "Средний рейтинг: " . round($stats_row['avg_rating'], 2) . "/5";
                    echo "<div class='info-box'>$statsText</div>";
                }
                $stats_stmt->close();
                
                echo "<div style='text-align: center;'>
                        <a href='form.html' class='back-link'>Добавить еще отзыв</a>
                        <a href='get_product.php?product_id=$product_id' class='back-link' style='background: #a5d6a7;'>Посмотреть товар</a>
                        <a href='view_reviews.php' class='back-link' style='background: #ba68c8;'>Все отзывы</a>
                      </div>";
                
            } else {
                throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            echo "<div class='error-box'>Произошла ошибка: " . $e->getMessage() . "</div>
                  <div style='text-align: center;'>
                      <a href='form.html' class='back-link'>Вернуться к форме</a>
                 </div>";
        }
    }


    echo "</div></body></html>";

} else {
    echo "<!DOCTYPE html>
    <html lang='ru'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Ошибка метода</title>
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
            <div class='error-box'>Метод не разрешен. Используйте POST запрос.</div>
            <div>
                <a href='form.html' class='back-link'>Перейти к форме</a>
                <a href='index.php' class='back-link' style='background: #a5d6a7;'>На главную</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

$con->close();
?>