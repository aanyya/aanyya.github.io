<?php
session_start();
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
require_once 'config.php';

// Обработка поискового запроса
$searchQuery = trim($_GET['search'] ?? '');

// Функция проверки покупки товара
function hasPurchasedProduct($pdo, $userId, $productId, $productCategory) {
    if (!$userId) return false;

    try {
        $sql = "
            SELECT 1 FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = :user_id
            AND oi.item_id = :product_id
            AND oi.product_category = :product_category
            AND o.status = 'completed'
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':product_category' => $productCategory
        ]);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        error_log("Ошибка при проверке покупки товара: " . $e->getMessage());
        return false;
    }
}

// Обработка добавления/удаления из избранного
if (isset($_GET['toggle_favorite'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Войдите, чтобы добавлять товары в избранное";
        header("Location: vhod.php?return_url=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    $userId = $_SESSION['user_id'];
    $productId = intval($_GET['item_id']);
    $productCategory = 'computer'; // Фиксированная категория для этой страницы

    try {
        // Проверяем существует ли товар
        $stmt = $pdo->prepare("SELECT id FROM items_computer WHERE id = ?");
        $stmt->execute([$productId]);

        if (!$stmt->fetch()) {
            $_SESSION['error'] = "Товар не найден";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Обработка избранного
        $pdo->beginTransaction();

        if (isFavorite($pdo, $userId, $productId, $productCategory)) {
            // Удаляем из избранного
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ?");
            $stmt->execute([$userId, $productId, $productCategory]);
            $_SESSION['success'] = "Товар удален из избранного";
        } else {
            // Добавляем в избранное
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id, product_category) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $productId, $productCategory]);
            $_SESSION['success'] = "Товар добавлен в избранное";
        }

        // Обновляем счетчик в сессии
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $_SESSION['favorites_count'] = $stmt->fetchColumn();

        $pdo->commit();

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Ошибка при обновлении избранного: " . $e->getMessage();
        error_log("Favorite error: User $userId, Product $productId, Category $productCategory. Error: " . $e->getMessage());
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

function isFavorite($pdo, $userId, $productId, $productCategory) {
    if (!$userId) return false;

    try {
        $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ? LIMIT 1");
        $stmt->execute([$userId, $productId, $productCategory]);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error checking favorite: " . $e->getMessage());
        return false;
    }
}

// Получаем информацию о избранных товарах и покупках пользователя
$favorites = [];
$purchases = [];
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Получаем избранные товары
    $stmt = $pdo->prepare("SELECT product_id, product_category FROM favorites WHERE user_id = ? AND product_category = 'computer'");
    $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем купленные товары
    $sql = "SELECT oi.item_id as product_id, oi.product_category
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ? AND oi.product_category = 'computer' AND o.status = 'completed'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем все товары для отображения
try {
    $allProducts = []; // Все товары без фильтрации (для поиска)
    $products = [];    // Товары после применения фильтров

    // Получаем все возможные фильтры для компьютерных очков
    $allTypes = $pdo->query("SELECT DISTINCT type FROM items_computer WHERE type IS NOT NULL AND type != ''")->fetchAll(PDO::FETCH_COLUMN);
    $allShapes = $pdo->query("SELECT DISTINCT shape FROM items_computer WHERE shape IS NOT NULL AND shape != ''")->fetchAll(PDO::FETCH_COLUMN);
    $allColors = $pdo->query("SELECT DISTINCT color FROM items_computer WHERE color IS NOT NULL AND color != ''")->fetchAll(PDO::FETCH_COLUMN);

    // Получаем товары
    $sql = "SELECT id, name, CAST(price AS DECIMAL(10,2)) as price, image, quantity, structure,
            'computer' AS category, type, color, shape, rating, popularity FROM items_computer";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Применяем поисковый запрос
    if (empty($searchQuery)) {
        $products = $allProducts;
    } else {
        foreach ($allProducts as $product) {
            if (stripos($product['name'], $searchQuery) !== false ||
                stripos($product['structure'] ?? '', $searchQuery) !== false ||
                stripos($product['type'] ?? '', $searchQuery) !== false ||
                stripos($product['color'] ?? '', $searchQuery) !== false ||
                stripos($product['shape'] ?? '', $searchQuery) !== false) {
                $products[] = $product;
            }
        }
    }

    // Применяем фильтры к уже отфильтрованным по поиску товарам
    $filteredProducts = [];
    foreach ($products as $product) {
        $matchesFilters = true;

        // Фильтр по типу
        if (!empty($_GET['types'])) {
            $selectedTypes = (array)$_GET['types'];
            $matchesFilters = $matchesFilters && in_array($product['type'], $selectedTypes);
        }

        // Фильтр по форме
        if (!empty($_GET['shapes'])) {
            $selectedShapes = (array)$_GET['shapes'];
            $matchesFilters = $matchesFilters && in_array($product['shape'], $selectedShapes);
        }

        // Фильтр по цвету
        if (!empty($_GET['colors'])) {
            $selectedColors = (array)$_GET['colors'];
            $matchesFilters = $matchesFilters && in_array($product['color'], $selectedColors);
        }

        if ($matchesFilters) {
            $filteredProducts[] = $product;
        }
    }

    $products = $filteredProducts;

    // Разделяем товары на доступные и отсутствующие
    $availableProducts = [];
    $outOfStockProducts = [];

    foreach ($products as $product) {
        if ($product['quantity'] > 0) {
            $availableProducts[] = $product;
        } else {
            $outOfStockProducts[] = $product;
        }
    }

    // Перемешиваем доступные товары
    shuffle($availableProducts);

    // Объединяем товары (сначала доступные, потом отсутствующие)
    $products = array_merge($availableProducts, $outOfStockProducts);

    // Применяем сортировку
    $sortBy = $_GET['sort'] ?? 'default';
    $sortOrder = $_GET['order'] ?? 'DESC';

    if ($sortBy !== 'default') {
        usort($products, function($a, $b) use ($sortBy, $sortOrder) {
            if ($sortBy === 'price' || $sortBy === 'rating' || $sortBy === 'popularity') {
                return $sortOrder === 'ASC'
                    ? $a[$sortBy] - $b[$sortBy]
                    : $b[$sortBy] - $a[$sortBy];
            } else {
                return $sortOrder === 'ASC'
                    ? strcmp($a[$sortBy], $b[$sortBy])
                    : strcmp($b[$sortBy], $a[$sortBy]);
            }
        });
    }

    // Популярные запросы для подсказок
    $popularSearches = [
        ["text" => "Компьютерные очки", "category" => "computer"],
        ["text" => "Очки для работы за компьютером", "category" => "computer"],
        ["text" => "Защитные очки от синего света", "category" => "computer"]
    ];

} catch (Exception $e) {
    die("Ошибка при загрузке товаров: " . $e->getMessage());
}

function getProductRating($pdo, $productId, $productCategory) {
    try {
        $stmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
            FROM product_reviews
            WHERE product_id = ? AND product_category = ? AND status = 'approved'
        ");
        $stmt->execute([$productId, $productCategory]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['review_count'] > 0) {
            return [
                'rating' => round($result['avg_rating'], 1),
                'count' => $result['review_count']
            ];
        }
        return null; // Нет отзывов
    } catch (PDOException $e) {
        error_log("Ошибка при получении рейтинга товара: " . $e->getMessage());
        return null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Каталог компьютерных очков для защиты глаз от синего света.
    ✅ Большой ассортимент - доступные цены за качество!
    ✅ Заказать и подобрать компьютерные очки - вы можете у нас на сайте.">
    <title>luw – компьютерные очки для защиты глаз</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="img/favicon.png" />

<style>
    /* Стили для поиска */
    .search-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 15px;
    }

    .search-box {
        position: relative;
        width: 100%;
        max-width: 800px;
        margin: 20px auto;
    }

    .search-form {
        display: flex;
    }

    .search-input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .search-button {
        padding: 12px 20px;
        background: #A95F1F;
        color: white;
        border: none;
        border-radius: 4px;
        margin-left: 10px;
        cursor: pointer;
    }

    .search-suggestions {
        display: none;
        position: absolute;
        width: 100%;
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ddd;
        background: white;
        z-index: 1000;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #eee;
    }

    .suggestion-item:hover {
        background: #f5f5f5;
    }

    .suggestion-item img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        margin-right: 10px;
    }

    .suggestion-item .suggestion-info {
        flex: 1;
    }

    .suggestion-item .suggestion-price {
        color: #A95F1F;
        font-weight: bold;
    }

    .suggestion-header {
        padding: 10px 15px;
        background: #f5f5f5;
        font-weight: bold;
        border-bottom: 1px solid #ddd;
    }

    .no-results {
        text-align: center;
        padding: 40px;
        font-size: 18px;
    }

    .popular-searches {
        margin-top: 20px;
    }

    .popular-searches h3 {
        margin-bottom: 10px;
    }

    .popular-searches ul {
        list-style: none;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

    .popular-searches li a {
        padding: 5px 10px;
        background: #f5f5f5;
        border-radius: 4px;
        color: #333;
        text-decoration: none;
    }

    .popular-searches li a:hover {
        background: #e0e0e0;
    }

    /* Стили для кнопок корзины */
    .add-to-cart-form {
        margin-top: 30px;
    }

    .add-to-cart-btn {
        background-color: #A95F1F;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
        width: 40%;
    }

    .login-to-add-btn {
        display: block;
        margin-top: 10px;
        text-align: center;
        color: #666;
        font-size: 13px;
        text-decoration: underline;
    }

    .login-to-add-btn:hover {
        color: #333;
    }

    .product-link {
        display: block;
        margin-top: 5px;
        color: white;
        font-size: 16px;
    }

    /* Стили для кнопки избранного */
    .favorite-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #ccc;
        z-index: 10;
    }

    .favorite-btn.active {
        color: red;
    }

    .favorite-btn:hover:not(.active) {
        color: #ff6b6b;
    }

    /*Модальное окно*/
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 300px;
        text-align: center;
        border-radius: 5px;
        position: relative;
    }

    .close-modal {
        position: absolute;
        top: 5px;
        right: 10px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-modal:hover {
        color: black;
    }

    .alert {
        padding: 15px;
        margin: 20px auto;
        max-width: 600px;
        border-radius: 4px;
        text-align: center;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .product-card.out-of-stock {
        opacity: 0.7;
        position: relative;
    }

    .product-card.out-of-stock::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 1;
    }

    .stock-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        z-index: 2;
        font-weight: bold;
    }

    .add-to-cart-btn.disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    /* Обновленные стили для формы отзыва */
    .leave-review-btn {
        display: block;
        margin-top: 15px;
        text-align: center;
        color: #666;
        font-size: 13px;
        cursor: pointer;
        text-decoration: underline;
    }

    .leave-review-btn:hover {
        color: #A95F1F;
    }

    .review-form {
        margin-top: 10px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 4px;
        animation: fadeIn 0.3s ease-out;
    }

    .review-form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        min-height: 100px;
        font-family: 'Montserrat', sans-serif;
    }

    .submit-review-btn {
        background-color: #A95F1F;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        width: 100%;
        transition: background-color 0.3s;
    }

    .submit-review-btn:hover {
        background-color: #8a4e1a;
    }

    .rating-stars {
        display: flex;
        justify-content: center;
        margin-bottom: 15px;
        direction: rtl;
    }

    .rating-stars input[type="radio"] {
        display: none;
    }

    .rating-stars label {
        font-size: 24px;
        color: #ccc;
        cursor: pointer;
        padding: 0 5px;
        transition: color 0.2s;
    }

    .rating-stars input[type="radio"]:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
        color: #ffc107;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Обновляем стили карточек для единого размера */
    .product-card {
        position: relative;
        transition: all 0.3s ease;
    }

    .product-info {
        min-height: 220px;
        display: flex;
        flex-direction: column;
    }

    /* Кнопка скролла вверх */
    .scroll-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #A95F1F;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 999;
    }

    .scroll-to-top.visible {
        opacity: 1;
    }

    .scroll-to-top:hover {
        background-color: #8a4e1a;
    }

    /* Стили для фильтров */
    .filters-top {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 15px;
    }

    .filters-form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .sorting-panel {
        width: 100%;
        margin-bottom: 20px;
    }

    .sorting-options {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sorting-options select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group h3 {
        cursor: pointer;
        padding: 10px;
        background-color: #f5f5f5;
        border-radius: 4px;
        margin: 0 0 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .filter-group h3 .arrow {
        transition: transform 0.3s;
    }

    .filter-group.active h3 .arrow {
        transform: rotate(90deg);
    }

    .filter-options {
        display: none;
        padding: 10px;
        background-color: #f9f9f9;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
    }

    .filter-group.active .filter-options {
        display: block;
    }

    .filter-option {
        display: block;
        margin-bottom: 8px;
    }

    .filter-option input {
        margin-right: 8px;
    }

    .reset-filters {
        padding: 10px 15px;
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 20px;
    }

    .reset-filters:hover {
        background-color: #e0e0e0;
    }
    .search-container, .filters-top {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 15px;
    }

    .search-box {
        position: relative;
        width: 100%;
        max-width: 800px;
        margin: 20px auto;
    }

    .search-form {
        display: flex;
    }

    .search-input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .search-button {
        padding: 12px 20px;
        background: #A95F1F;
        color: white;
        border: none;
        border-radius: 4px;
        margin-left: 10px;
        cursor: pointer;
    }

    /* Стили для фильтров в черных тонах */
    .filters-form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .sorting-panel {
        width: 100%;
        margin-bottom: 20px;
    }

    .sorting-options {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sorting-options select {
        padding: 8px 12px;
        border: 1px solid #333;
        border-radius: 4px;
        font-size: 14px;
        background-color: #222;
        color: #fff;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group h3 {
        cursor: pointer;
        padding: 10px;
        background-color: #222;
        color: #fff;
        border-radius: 4px;
        margin: 0 0 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s;
    }

    .filter-group h3:hover {
        background-color: #333;
    }

    .filter-group h3 .arrow {
        transition: transform 0.3s;
    }

    .filter-group.active h3 .arrow {
        transform: rotate(90deg);
    }

    .filter-options {
        display: none;
        padding: 10px;
        background-color: #333;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
    }

    .filter-group.active .filter-options {
        display: block;
    }

    .filter-option {
        display: block;
        margin-bottom: 8px;
        color: #fff;
    }

    .filter-option input {
        margin-right: 8px;
    }

    .reset-filters {
        padding: 10px 15px;
        background-color: #222;
        color: #fff;
        border: 1px solid #444;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 20px;
        transition: background-color 0.3s;
    }

    .reset-filters:hover {
        background-color: #333;
    }

    /* Стили для карточек товаров */
    .product-card {
        position: relative;
        transition: all 0.3s ease;
    }

    .product-card.out-of-stock {
        opacity: 0.7;
        position: relative;
    }

    .product-card.out-of-stock::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 1;
    }

    .stock-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        z-index: 2;
        font-weight: bold;
    }

    /* Стили для кнопки избранного */
    .favorite-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #ccc;
        z-index: 10;
    }

    .favorite-btn.active {
        color: red;
    }

    .favorite-btn:hover:not(.active) {
        color: #ff6b6b;
    }

    /* Кнопка скролла вверх */
    .scroll-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #A95F1F;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 999;
    }

    .scroll-to-top.visible {
        opacity: 1;
    }

    .scroll-to-top:hover {
        background-color: #8a4e1a;
    }

    /* Уведомления */
    .alert {
        padding: 15px;
        margin: 20px auto;
        max-width: 600px;
        border-radius: 4px;
        text-align: center;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .product-rating.no-rating {
    color: #999;
    font-size: 0.9em;
}

@media (max-width: 1024px) {
    .categories {
        flex-wrap: wrap;
        justify-content: center;
    }

    .plate, .plate-foto2, .plate-foto3, .plate-foto4 {
        width: 45%;
        margin-bottom: 15px;
    }

    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .filter-group {
        min-width: 150px;
    }

    .product-info {
        min-height: 200px;
    }

    .add-to-cart-btn {
        width: 50%;
    }
}


@media (max-width: 768px) {
    .block-collection h1 {
        font-size: 28px;
        text-align: center;
    }

    .categories {
        flex-direction: column;
        align-items: center;
    }

    .plate, .plate-foto2, .plate-foto3, .plate-foto4 {
        width: 90%;
        margin-bottom: 10px;
    }

    .search-box {
        margin: 10px auto;
    }

    .search-input {
        padding: 10px 12px;
        font-size: 14px;
    }

    .search-button {
        padding: 10px 15px;
    }

    .filters-form {
        flex-direction: column;
        gap: 10px;
    }

    .filter-group {
        width: 100%;
    }

    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .product-card {
        padding: 10px;
    }

    .product-title {
        font-size: 14px;
    }

    .product-price {
        font-size: 16px;
    }

    .product-rating {
        font-size: 12px;
    }

    .product-link {
        font-size: 14px;
    }

    .add-to-cart-btn {
        width: 100%;
        padding: 6px 10px;
        font-size: 12px;
    }

    .quantity-input {
        width: 40px;
        padding: 4px;
    }

    .favorite-btn {
        font-size: 16px;
    }

    .scroll-to-top {
        width: 40px;
        height: 40px;
        bottom: 15px;
        right: 15px;
    }
}


@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }

    .product-card {
        max-width: 100%;
    }

    .sorting-options {
        flex-direction: column;
        align-items: flex-start;
    }

    .sorting-options select {
        width: 100%;
        margin-bottom: 5px;
    }

    .search-suggestions {
        max-height: 300px;
    }

    .suggestion-item {
        padding: 8px 10px;
    }

    .suggestion-item img {
        width: 30px;
        height: 30px;
    }
}
</style>
</head>
<body class="b-c">
<?php include 'header.php'; ?>

    <div class="block-collection">
        <div class="breadcrumbs">
            <a class="hierarchy" href="index.php">Главная</a>
            <img src="img/arrow-right.svg" alt="">
            <a class="hierarchy" href="collection_glasses.php">Каталог</a>
            <img src="img/arrow-right.svg" alt="">
            <a class="hierarchy" href="computer.php">Компьютерные очки</a>
        </div>
        <h1 class="gradient">Компьютерные очки</h1>

    <div>
        <div class="categories">
            <a class="plate" href="image.php">
                <img class="plate-arrow" src="img/up-arrow-right.svg" alt="">
                <span class="plate-next">имиджевые</span>
            </a>
            <a class="plate-foto2" href="vision.php">
                <img class="plate-arrow" src="img/up-arrow-right.svg" alt="">
                <span class="plate-next">для зрения</span>
            </a>
            <a class="plate-foto3" href="sun.php">
                <img class="plate-arrow" src="img/up-arrow-right.svg" alt="">
                <span class="plate-next">солнцезащитные</span>
            </a>
            <a class="plate-foto4" href="computer.php">
                <img class="plate-arrow" src="img/up-arrow-right.svg" alt="">
                <span class="plate-next">компьютерные</span>
            </a>
        </div>
    </div>
    </div>

<div class="catalog-container">
    <!-- Поисковая строка -->
    <div class="search-container">
        <div class="search-box">
            <form class="search-form" id="searchForm">
                <input type="text" class="search-input" id="searchInput" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Поиск...">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>
    </div>

    <!-- Фильтры и сортировка -->
    <div class="filters-top">
        <form id="filters-form" class="filters-form">
            <div class="sorting-panel">
                <div class="sorting-options">
                    <span>Сортировка:</span>
                    <select id="sort-by" name="sort">
                        <option value="default" <?= ($_GET['sort'] ?? 'default') === 'default' ? 'selected' : '' ?>>По умолчанию</option>
                        <option value="price" <?= ($_GET['sort'] ?? '') === 'price' ? 'selected' : '' ?>>По цене</option>
                        <option value="rating" <?= ($_GET['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>По рейтингу</option>
                        <option value="popularity" <?= ($_GET['sort'] ?? '') === 'popularity' ? 'selected' : '' ?>>По популярности</option>
                    </select>
                    <select id="sort-order" name="order">
                        <option value="DESC" <?= ($_GET['order'] ?? 'DESC') === 'DESC' ? 'selected' : '' ?>>По убыванию</option>
                        <option value="ASC" <?= ($_GET['order'] ?? '') === 'ASC' ? 'selected' : '' ?>>По возрастанию</option>
                    </select>
                </div>
            </div>

            <?php if (!empty($allTypes)): ?>
            <div class="filter-group">
                <h3>Тип <span class="arrow">→</span></h3>
                <div class="filter-options">
                    <?php foreach ($allTypes as $type): ?>
                        <label class="filter-option">
                            <input type="checkbox" name="types[]" value="<?= htmlspecialchars($type) ?>"
                                <?= isset($_GET['types']) && in_array($type, (array)$_GET['types']) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($type) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($allShapes)): ?>
            <div class="filter-group">
                <h3>Форма <span class="arrow">→</span></h3>
                <div class="filter-options">
                    <?php foreach ($allShapes as $shape): ?>
                        <label class="filter-option">
                            <input type="checkbox" name="shapes[]" value="<?= htmlspecialchars($shape) ?>"
                                <?= isset($_GET['shapes']) && in_array($shape, (array)$_GET['shapes']) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($shape) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($allColors)): ?>
            <div class="filter-group">
                <h3>Цвет <span class="arrow">→</span></h3>
                <div class="filter-options">
                    <?php foreach ($allColors as $color): ?>
                        <label class="filter-option">
                            <input type="checkbox" name="colors[]" value="<?= htmlspecialchars($color) ?>"
                                <?= isset($_GET['colors']) && in_array($color, (array)$_GET['colors']) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($color) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <button type="button" id="reset-filters" class="reset-filters">Сбросить все</button>
        </form>
    </div>

    <div class="products-container">
        <div class="products-grid">
            <?php if (empty($products) && !empty($searchQuery)): ?>
                <div class="no-results">
                    Ничего не найдено по запросу "<?= htmlspecialchars($searchQuery) ?>". Попробуйте изменить параметры поиска.
                    <div class="popular-searches">
                        <h3>Популярные запросы:</h3>
                        <ul>
                            <?php foreach ($popularSearches as $search): ?>
                                <li><a href="?search=<?= urlencode($search['text']) ?>"><?= htmlspecialchars($search['text']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product):
                    $userId = $_SESSION['user_id'] ?? null;
                    $hasPurchased = $userId ? hasPurchasedProduct($pdo, $userId, $product['id'], $product['category']) : false;
                    $isFavorite = $userId ? isFavorite($pdo, $userId, $product['id'], $product['category']) : false;
                    $isOutOfStock = $product['quantity'] <= 0;

                    // Получаем реальный рейтинг из отзывов
                    $productRating = getProductRating($pdo, $product['id'], $product['category']);
                ?>
                    <div class="product-card <?= $isOutOfStock ? 'out-of-stock' : '' ?>"
                         data-id="<?= $product['id'] ?>"
                         data-category="<?= $product['category'] ?>"
                         data-type="<?= $product['type'] ?? '' ?>"
                         data-color="<?= htmlspecialchars($product['color']) ?>"
                         data-shape="<?= htmlspecialchars($product['shape'] ?? '') ?>"
                         data-price="<?= str_replace([' ', 'р.', '₽'], '', $product['price']) ?>"
                         data-rating="<?= $productRating ? $productRating['rating'] : 0 ?>"
                         data-popularity="<?= $product['popularity'] ?>"
                         data-quantity="<?= $product['quantity'] ?>">

                        <?php if ($isOutOfStock): ?>
                            <div class="stock-label">Нет в наличии</div>
                        <?php endif; ?>

                        <a href="?toggle_favorite=1&item_id=<?= $product['id'] ?>&item_type=computer"
                           class="favorite-btn <?= $isFavorite ? 'active' : '' ?>"
                           onclick="return confirmFavoriteAction(this, event)">
                            <i class="fas fa-heart"></i>
                        </a>

                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-price-rating">
                                <p class="product-price"><?= htmlspecialchars($product['price']) ?> ₽</p>
                                <?php if ($productRating): ?>
                                    <div class="product-rating" title="Рейтинг: <?= $productRating['rating'] ?> (<?= $productRating['count'] ?> отзывов)">
                                        ★ <?= $productRating['rating'] ?>/5
                                    </div>
                                <?php else: ?>
                                    <div class="product-rating no-rating" title="Пока нет отзывов">
                                        Нет отзывов
                                    </div>
                                <?php endif; ?>
                            </div>

                            <a href="product_detail.php?id=<?= $product['id'] ?>&category=computer" class="product-link">подробнее</a>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($product['quantity'] > 0): ?>
                                    <form class="add-to-cart-form" method="POST" action="cart.php">
                                        <input type="hidden" name="item_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="item_name" value="<?= htmlspecialchars($product['name']) ?>">
                                        <input type="hidden" name="item_price" value="<?= $product['price'] ?>">
                                        <input type="hidden" name="category" value="computer">
                                        <input type="hidden" name="add_to_cart" value="1">
                                        <button type="submit" class="add-to-cart-btn">в корзину</button>
                                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['quantity'] ?>" class="quantity-input">
                                    </form>
                                <?php else: ?>
                                    <button class="add-to-cart-btn disabled" disabled>Нет в наличии</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="vhod.php" class="login-to-add-btn">Войдите, чтобы добавить в корзину</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Кнопка скролла вверх -->
<div class="scroll-to-top" id="scrollToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция для подтверждения действия с избранным
    function confirmFavoriteAction(link, event) {
        event.preventDefault();

        // Проверка авторизации
        <?php if (!isset($_SESSION['user_id'])): ?>
            alert('Войдите, чтобы добавлять товары в избранное');
            window.location.href = 'vhod.php?return_url=' + encodeURIComponent(window.location.href);
            return false;
        <?php endif; ?>

        // Подтверждение для удаления
        if (link.classList.contains('active')) {
            if (!confirm('Удалить из избранного?')) {
                return false;
            }
        }

        window.location.href = link.href;
        return false;
    }

    // Функции для работы с фильтрами
    document.querySelectorAll('.filter-group h3').forEach(header => {
        header.addEventListener('click', function() {
            const group = this.closest('.filter-group');
            const isActive = group.classList.contains('active');

            document.querySelectorAll('.filter-group').forEach(g => {
                if (g !== group) g.classList.remove('active');
            });

            group.classList.toggle('active', !isActive);
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.filter-group') && !e.target.closest('.sorting-options')) {
            document.querySelectorAll('.filter-group').forEach(group => {
                group.classList.remove('active');
            });
        }
    });

    // Сброс всех фильтров, сортировки и поиска
    document.getElementById('reset-filters').addEventListener('click', function() {
        // Очищаем все чекбоксы
        document.querySelectorAll('.filter-options input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Сбрасываем сортировку
        document.getElementById('sort-by').value = 'default';
        document.getElementById('sort-order').value = 'DESC';

        // Очищаем поиск
        document.getElementById('searchInput').value = '';

        // Скрываем открытые фильтры
        document.querySelectorAll('.filter-group').forEach(group => {
            group.classList.remove('active');
        });

        // Перенаправляем на чистую страницу без параметров
        window.location.href = window.location.pathname;
    });

    // Применение фильтров при изменении параметров
    document.querySelectorAll('#filters-form input, #sort-by, #sort-order').forEach(element => {
        element.addEventListener('change', function() {
            const form = document.getElementById('filters-form');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams();

            // Добавляем параметры формы
            for (const [key, value] of formData.entries()) {
                if (value) {
                    searchParams.append(key, value);
                }
            }

            // Добавляем параметр поиска, если он есть
            const searchInput = document.getElementById('searchInput');
            if (searchInput.value) {
                searchParams.set('search', searchInput.value);
            }

            // Обновляем URL без перезагрузки страницы
            const newUrl = window.location.pathname + '?' + searchParams.toString();
            window.history.pushState({}, '', newUrl);

            // Перезагружаем страницу для применения фильтров
            window.location.reload();
        });
    });

    // Поиск товаров
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const allProducts = <?php echo json_encode($allProducts); ?>;

    // Функция для фильтрации товаров
    function filterProducts(searchTerm) {
        if (!searchTerm) return allProducts;

        const term = searchTerm.toLowerCase();
        return allProducts.filter(product => {
            return (
                product.name.toLowerCase().includes(term) ||
                (product.structure && product.structure.toLowerCase().includes(term)) ||
                (product.color && product.color.toLowerCase().includes(term)) ||
                (product.type && product.type.toLowerCase().includes(term)) ||
                (product.shape && product.shape.toLowerCase().includes(term))
            );
        });
    }

    // Функция для отображения подсказок
    function showSuggestions(results) {
        searchSuggestions.innerHTML = '';

        if (results.length === 0) {
            searchSuggestions.innerHTML = '<div class="no-results">Ничего не найдено</div>';
            searchSuggestions.style.display = 'block';
            return;
        }

        const categoryHeader = document.createElement('div');
        categoryHeader.className = 'suggestion-header';
        categoryHeader.textContent = 'Компьютерные очки';
        searchSuggestions.appendChild(categoryHeader);

        results.slice(0, 5).forEach(product => {
            const item = document.createElement('a');
            item.className = 'suggestion-item';
            item.href = `product_detail.php?id=${product.id}&category=computer`;

            item.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <div class="suggestion-info">
                    <div>${product.name}</div>
                    <div class="suggestion-price">${product.price} ₽</div>
                </div>
            `;
            searchSuggestions.appendChild(item);
        });

        searchSuggestions.style.display = 'block';
    }

    // Обработчики событий
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        if (searchTerm.length >= 2) {
            const results = filterProducts(searchTerm);
            showSuggestions(results);
        } else {
            searchSuggestions.style.display = 'none';
        }
    });

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            // Добавляем параметр search в URL
            const url = new URL(window.location);
            url.searchParams.set('search', searchTerm);
            window.history.pushState({}, '', url);

            // Перезагружаем страницу для применения поиска
            window.location.reload();
        }
        searchSuggestions.style.display = 'none';
    });

    // Обработка клика вне поля поиска для скрытия подсказок
    document.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target)) {
            searchSuggestions.style.display = 'none';
        }
    });

    // Обработка параметра search из URL
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    if (searchParam) {
        searchInput.value = searchParam;
    }

    // Скролл вверх
    const scrollToTopBtn = document.getElementById('scrollToTop');

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });

    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>
</body>
</html>