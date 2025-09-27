<?php
// delete_review.php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Проверяем, принадлежит ли отзыв текущему пользователю
        $stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$review_id, $user_id]);

        if ($stmt->fetch()) {
            // Удаляем отзыв
            $delete_stmt = $pdo->prepare("DELETE FROM product_reviews WHERE id = ?");
            $delete_stmt->execute([$review_id]);

            $_SESSION['success_message'] = "Отзыв успешно удален";
        } else {
            $_SESSION['error_message'] = "Вы не можете удалить этот отзыв";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Ошибка при удалении отзыва: " . $e->getMessage();
    }
}

header("Location: my_reviews.php");
exit;
?>