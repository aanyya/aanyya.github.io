<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Для добавления в избранное необходимо авторизоваться']);
    exit();
}

$productId = (int)($_POST['item_id'] ?? 0);
$productCategory = $_POST['item_type'] ?? '';

// Проверяем допустимые категории
$allowedCategories = ['lenses', 'sun', 'vision', 'image', 'computer'];
if (!$productId || !in_array($productCategory, $allowedCategories)) {
    echo json_encode(['success' => false, 'message' => 'Некорректные параметры товара']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Проверяем, есть ли уже в избранном
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ?");
    $stmt->execute([$_SESSION['user_id'], $productId, $productCategory]);
    $isFavorite = (bool)$stmt->fetch();

    if ($isFavorite) {
        // Удаляем из избранного
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ?");
        $stmt->execute([$_SESSION['user_id'], $productId, $productCategory]);
        $message = "Товар удален из избранного";
        $newStatus = false;
    } else {
        // Добавляем в избранное
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id, product_category) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $productId, $productCategory]);
        $message = "Товар добавлен в избранное";
        $newStatus = true;
    }

    // Получаем обновленное количество избранных товаров
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetchColumn();

    $pdo->commit();

    // Обновляем счетчик в сессии
    $_SESSION['favorites_count'] = $count;

    echo json_encode([
        'success' => true,
        'isFavorite' => $newStatus,
        'count' => $count,
        'message' => $message
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}