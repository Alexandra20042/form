<?php
require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Получаем и очищаем данные из формы
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
    if (!empty($errors)) {
        http_response_code(400);
        echo "Ошибки валидации:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
        exit;
    }

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
                http_response_code(400);
                echo "Ошибка: Товар с ID $product_id уже существует!\n";
                echo "Существующий товар: $existing_name\n";
                echo "Введенный товар: $product_name\n";
                echo "Пожалуйста, используйте правильное название товара или измените ID.";
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
            http_response_code(200);
            echo "Данные успешно сохранены!\n";
            echo "ID товара: $product_id\n";
            echo "Название товара: $product_name\n";
            echo "Имя рецензента: $reviewer_name\n";
            echo "Email: $reviewer_email\n";
            echo "Текст отзыва: $review_text\n";
            echo "Оценка: $rating/5";
            
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
                echo "\n\nСтатистика по товару:\n";
                echo "Всего отзывов: {$stats_row['total_reviews']}\n";
                echo "Средний рейтинг: " . round($stats_row['avg_rating'], 2) . "/5";
            }
            $stats_stmt->close();
            
        } else {
            throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
        }
        
    
        $stmt->close();
        
    } catch (Exception $e) {
        http_response_code(500);
        echo "Произошла ошибка: " . $e->getMessage();
    }
    
} else {

    http_response_code(405);
    echo "Метод не разрешен. Используйте POST запрос.";
    exit;
}

$con->close();
?>