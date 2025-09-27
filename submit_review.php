<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Войдите, чтобы оставить отзыв";
    header("Location: vhod.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $productCategory = $_POST['product_category'];
    $rating = $_POST['rating'];
    $reviewText = $_POST['review_text'];

    // Проверяем, не оставлял ли пользователь уже отзыв
    $stmt = $pdo->prepare("SELECT 1 FROM product_reviews WHERE user_id = ? AND product_id = ? AND product_category = ?");
    $stmt->execute([$userId, $productId, $productCategory]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = "Вы уже оставляли отзыв на этот товар";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Проверяем, покупал ли пользователь товар
    $stmt = $pdo->prepare("
        SELECT 1 FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.item_id = ? AND oi.product_category = ? AND o.status = 'completed'
    ");
    $stmt->execute([$userId, $productId, $productCategory]);

    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Вы можете оставлять отзывы только на купленные товары";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Добавляем отзыв
    try {
        $stmt = $pdo->prepare("
            INSERT INTO product_reviews (user_id, product_id, product_category, rating, review_text, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'approved', NOW())
        ");
        $stmt->execute([$userId, $productId, $productCategory, $rating, $reviewText]);

        $_SESSION['success'] = "Спасибо за ваш отзыв!";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при сохранении отзыва: " . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>