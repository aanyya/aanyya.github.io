<?php
include 'config.php';
session_start();

if (!isset($_GET['id']) || !isset($_GET['category'])) {
    header('Location: collection_glasses.php');
    exit();
}

$id = (int)$_GET['id'];
$category = $_GET['category'];

// Определяем таблицу в зависимости от категории
$tableMap = [
    'lenses' => 'items_lenses',
    'sun' => 'items_sun',
    'vision' => 'items_vision',
    'image' => 'items_image',
    'computer' => 'items_computer'
];

if (!array_key_exists($category, $tableMap)) {
    header('Location: collection_glasses.php');
    exit();
}

$table = $tableMap[$category];

// Получаем данные товара
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: collection_glasses.php');
    exit();
}

// Проверяем, покупал ли пользователь этот товар
$has_purchased = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o
                          JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.user_id = ? AND oi.item_id = ? AND oi.product_category = ? AND o.status = 'completed'");
    $stmt->execute([$_SESSION['user_id'], $id, $category]);
    $has_purchased = $stmt->fetchColumn() > 0;
}

// Проверяем, есть ли товар в избранном
$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id'], $id, $category]);
    $isFavorite = (bool)$stmt->fetch();
}
// Обработка удаления из избранного
if (isset($_GET['remove_favorite'])) {
    $productId = (int)$_GET['product_id'];
    $productCategory = $_GET['product_category'];

    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ?");
        $stmt->execute([$userId, $productId, $productCategory]);

        $_SESSION['success'] = "Товар удален из избранного";
        header("Location: favorites.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при удалении из избранного: " . $e->getMessage();
        header("Location: favorites.php");
        exit();
    }
}

try {
    // Получаем избранные товары пользователя из базы данных
    $stmt = $pdo->prepare("
        SELECT
            f.product_id as id,
            f.product_category as category,
            i.name,
            i.price,
            i.image,
            i.quantity,
            i.type,
            i.color,
            i.shape,
            i.rating
        FROM favorites f
        LEFT JOIN (
            SELECT id, name, price, image, quantity, type, color, shape, rating, 'lenses' as category FROM items_lenses
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, 'sun' FROM items_sun
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, 'vision' FROM items_vision
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, 'image' FROM items_image
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, 'computer' FROM items_computer
        ) i ON f.product_id = i.id AND f.product_category = i.category
        WHERE f.user_id = ?
    ");
    $stmt->execute([$userId]);
    $fullFavorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Обновляем счетчик в сессии
    $_SESSION['favorites_count'] = count($fullFavorites);
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
// Получаем одобренные отзывы для этого товара
$reviews_stmt = $pdo->prepare("SELECT pr.*, u.username
                              FROM product_reviews pr
                              JOIN users u ON pr.user_id = u.id
                              WHERE pr.product_id = ? AND pr.product_category = ? AND pr.status = 'approved'
                              ORDER BY
                                CASE WHEN pr.user_id = ? THEN 0 ELSE 1 END,
                                pr.created_at DESC");
$user_id = $_SESSION['user_id'] ?? 0;
$reviews_stmt->execute([$id, $category, $user_id]);
$reviews = $reviews_stmt->fetchAll();

// Проверяем, есть ли отзыв от текущего пользователя
$user_review = null;
if (isset($_SESSION['user_id'])) {
    foreach ($reviews as $review) {
        if ($review['user_id'] == $_SESSION['user_id']) {
            $user_review = $review;
            break;
        }
    }
}

// Обработка добавления в избранное
if (isset($_GET['toggle_favorite'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Войдите, чтобы добавлять товары в избранное";
        header("Location: vhod.php?return_url=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    $userId = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        if ($isFavorite) {
            // Удаляем из избранного
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ?");
            $stmt->execute([$userId, $id, $category]);
            $_SESSION['success'] = "Товар удален из избранного";
            $isFavorite = false;
        } else {
            // Добавляем в избранное
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id, product_category) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $id, $category]);
            $_SESSION['success'] = "Товар добавлен в избранное";
            $isFavorite = true;
        }

        // Обновляем счетчик в сессии
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $_SESSION['favorites_count'] = $stmt->fetchColumn();

        $pdo->commit();

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Ошибка при обновлении избранного: " . $e->getMessage();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Обработка отзывов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Войдите, чтобы оставлять отзывы";
        header("Location: vhod.php?return_url=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    $userId = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $reviewText = trim($_POST['review_text']);

    // Проверка данных
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Неверный рейтинг";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    if (empty($reviewText)) {
        $_SESSION['error'] = "Текст отзыва не может быть пустым";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Проверка покупки товара
    if (!$has_purchased) {
        $_SESSION['error'] = "Вы можете оставить отзыв только на купленные товары";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Проверка, не оставлял ли уже пользователь отзыв
    $checkStmt = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ? AND product_category = ?");
    $checkStmt->execute([$userId, $id, $category]);

    if ($checkStmt->fetch()) {
        $_SESSION['error'] = "Вы уже оставляли отзыв на этот товар";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    try {
        // Добавляем отзыв
        $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, product_category, user_id, rating, review_text, status, created_at)
                              VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$id, $category, $userId, $rating, $reviewText]);

        $_SESSION['success'] = "Ваш отзыв отправлен на модерацию. Спасибо!";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при сохранении отзыва: " . $e->getMessage();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Каталог стильных очков и оправ по выгодной цене в Москве.
    ✅ Большой ассортимент - доступные цены за качество!
    ✅ Заказать и подобрать очки - вы можете у нас на сайте.">
    <title>luw – всё о лучших очках</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="img/favicon.png" />
    <style>
        :root {
            --bg-color: #0e0e0e;
            --text-color: #FFF;
            --secondary-text: #AAA;
            --button-color: #B5E253;
            --button-text: #000;
            --border-color: #333;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            padding-top: 100px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: var(--text-color);
            text-decoration: none;
            font-size: 16px;
            position: relative;
            padding-left: 20px;
        }

        .back-link:before {
            content: "←";
            position: absolute;
            left: 0;
        }

        .product-container {
            display: flex;
            gap: 40px;
            margin-bottom: 60px;
            flex-wrap: wrap;
        }

        .product-image {
            flex: 1;
            min-width: 300px;
            max-width: 451px;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: auto;
            max-height: 600px;
            object-fit: contain;
            border: 1px solid var(--border-color);
        }

        .product-info {
            flex: 1;
            min-width: 300px;
        }

        .product-title {
            font-size: 28px;
            margin: 0 0 10px;
            font-weight: 500;
        }

        .product-price {
            font-size: 24px;
            margin: 0 0 20px;
            font-weight: 500;
        }

        .product-description {
            margin: 20px 0;
            color: var(--text-color);
        }

        .product-specs {
            margin: 30px 0;
        }

        .spec-item {
            display: flex;
            margin-bottom: 10px;
        }

        .spec-name {
            width: 90px;
            color: var(--secondary-text);
        }

        .spec-value {
            flex: 1;
            color: var(--secondary-text);
        }

        .action-buttons {
            margin: 30px 0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            background-color: var(--button-color);
            color: var(--button-text);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

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

        .favorite-btn.disabled {
            color: #666;
            cursor: not-allowed;
        }

        .reviews-section {
            margin-top: 60px;
            border-top: 1px solid var(--border-color);
            padding-top: 30px;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .reviews-title {
            font-size: 22px;
            margin: 0;
        }

        .review-form {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .rating-stars {
            display: flex;
            margin: 10px 0;
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

        .reviews-list {
            margin-top: 30px;
        }

        .review-item {
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .review-author {
            font-weight: 500;
        }

        .review-date {
            color: var(--secondary-text);
        }

        .review-rating {
            color: gold;
            margin-bottom: 10px;
        }

        .review-text {
            margin: 0;
            white-space: pre-line;
        }

        .also-viewed {
            margin-top: 60px;
            border-top: 1px solid var(--border-color);
            padding-top: 30px;
        }

        .also-viewed-title {
            font-size: 22px;
            margin: 0 0 20px;
        }

        .also-viewed-items {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .also-viewed-item {
            min-width: 200px;
        }

        .also-viewed-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .also-viewed-item-title {
            font-size: 14px;
            margin: 0 0 5px;
        }

        .also-viewed-item-price {
            font-size: 16px;
            color: var(--text-color);
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

        .out-of-stock-label {
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
                .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .remove-favorite-btn {
            background-color: transparent;
            color: var(--error);
            border: 1px solid var(--error);
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }

        .remove-favorite-btn:hover {
            background-color: var(--error);
            color: white;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
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
            .lens-options {
        margin: 20px 0;
        border: 1px solid var(--border-color);
        padding: 15px;
        border-radius: 4px;
    }

    .lens-option {
        margin-bottom: 15px;
    }

    .lens-option:last-child {
        margin-bottom: 0;
    }

    .lens-option label {
        display: block;
        margin-bottom: 5px;
        color: var(--secondary-text);
    }
   .favorite-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.5);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        color: #ccc;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .favorite-btn:hover {
        background: rgba(0, 0, 0, 0.7);
        color: #ff6b6b;
    }

    .favorite-btn.active {
        color: red;
        background: rgba(0, 0, 0, 0.7);
    }

    .favorite-btn.disabled {
        color: #666;
        cursor: not-allowed;
        background: rgba(0, 0, 0, 0.3);
    }

    .favorite-btn.disabled:hover {
        color: #666;
        background: rgba(0, 0, 0, 0.3);
    }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <a href="collection_glasses.php" class="back-link">Вернуться назад</a>

    <div class="product-container">
        <div class="product-image">
            <?php if ($product['quantity'] <= 0): ?>
                <div class="out-of-stock-label">Нет в наличии</div>
            <?php endif; ?>

    <button class="favorite-btn <?= $isFavorite ? 'active' : '' ?> <?= $product['quantity'] <= 0 ? 'disabled' : '' ?>"
            data-product-id="<?= $id ?>"
            data-product-category="<?= $category ?>">
        <i class="fas fa-heart"></i>
    </button>

            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price"><?= htmlspecialchars($product['price']) ?> ₽</p>

            <?php if (!empty($product['structure'])): ?>
            <div class="product-description">
                <?= htmlspecialchars($product['structure']) ?>
            </div>
            <?php endif; ?>

            <div class="product-specs">
                <div class="spec-item">
                    <span class="spec-name">Цвет:</span>
                    <span class="spec-value"><?= htmlspecialchars($product['color']) ?></span>
                </div>
                <?php if (!empty($product['type'])): ?>
                <div class="spec-item">
                    <span class="spec-name">Тип:</span>
                    <span class="spec-value"><?= htmlspecialchars($product['type']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['shape'])): ?>
                <div class="spec-item">
                    <span class="spec-name">Форма:</span>
                    <span class="spec-value"><?= htmlspecialchars($product['shape']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['quantity'])): ?>
                <div class="spec-item">
                    <span class="spec-name">Доступно:</span>
                    <span class="spec-value"><?= htmlspecialchars($product['quantity']) ?> шт.</span>
                </div>
                <?php endif; ?>
            </div>

           <?php if ($category === 'lenses'): ?>
            <div class="lens-options">
                <div class="lens-option">
                    <label for="optical_power">Оптическая сила:</label>
                    <select id="optical_power" name="optical_power" class="form-control" required>
                        <option value="">Выберите</option>
                        <?php for ($i = -9; $i <= 12; $i += 0.25): ?>
                            <option value="<?= number_format($i, 2) ?>"><?= number_format($i, 2) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="lens-option">
                    <label for="base_curve">Оптическая кривизна:</label>
                    <select id="base_curve" name="base_curve" class="form-control" required>
                        <option value="">Выберите</option>
                        <option value="8.5">8.5</option>
                        <option value="8.6">8.6</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($product['quantity'] > 0): ?>
                        <button class="btn add-to-cart">Добавить в корзину</button>
                    <?php else: ?>
                        <button class="btn disabled" disabled>Нет в наличии</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="vhod.php?return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn">Войдите, чтобы добавить в корзину</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="reviews-section">
        <div class="reviews-header">
            <h2 class="reviews-title">Отзывы</h2>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($has_purchased): ?>
                <?php if ($user_review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-author">Ваш отзыв</span>
                            <span class="review-date"><?= date('d.m.Y', strtotime($user_review['created_at'])) ?></span>
                        </div>
                        <div class="review-rating"><?= str_repeat('★', $user_review['rating']) ?></div>
                        <p class="review-text"><?= nl2br(htmlspecialchars($user_review['review_text'])) ?></p>
                    </div>
                <?php else: ?>
                    <button id="show-review-form" class="btn">Добавить отзыв</button>
                    <div id="review-form" class="review-form" style="display:none;">
                        <h3>Оставить отзыв</h3>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $id ?>">
                            <input type="hidden" name="product_category" value="<?= $category ?>">

                            <div class="rating-stars">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1">★</label>
                            </div>

                            <textarea name="review_text" rows="4" style="width:100%;" placeholder="Ваш отзыв..." required></textarea>
                            <button type="submit" name="submit_review" class="btn">Отправить на модерацию</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <a href="vhod.php?return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn">Войдите, чтобы оставить отзыв</a>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <?php if (isset($_SESSION['user_id']) && $review['user_id'] == $_SESSION['user_id']) continue; ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-author"><?= htmlspecialchars($review['username']) ?></span>
                            <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
                        </div>
                        <div class="review-rating"><?= str_repeat('★', $review['rating']) ?></div>
                        <p class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Пока нет отзывов на этот товар.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function confirmFavoriteAction(link, event) {
        event.preventDefault();

        // Если кнопка disabled, ничего не делаем
        if (link.classList.contains('disabled')) {
            return false;
        }

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

    $(document).ready(function() {
        // Обработка добавления в корзину
        $('.add-to-cart').click(function() {
            const productId = <?= $id ?>;
            const productName = "<?= addslashes($product['name']) ?>";
            const productPrice = "<?= $product['price'] ?>";
            const category = "<?= $category ?>";
            const quantity = 1;

            $.post('cart.php', {
                add_to_cart: true,
                item_id: productId,
                item_name: productName,
                item_price: productPrice,
                category: category,
                quantity: quantity
            }, function(response) {
                if (response.success) {
                    alert(response.message);
                    // Обновляем счетчик корзины в шапке
                    if (response.cart_count !== undefined) {
                        $('.cart-count').text(response.cart_count).show();
                    }
                } else {
                    alert(response.message);
                }
            }, 'json').fail(function() {
                alert('Товар добавлен в корзину');
            });
        });

        // Показать/скрыть форму отзыва
        $('#show-review-form').click(function() {
            $('#review-form').show();
            $(this).hide();
        });

        // Инициализация звезд рейтинга
        $('.rating-stars input[type="radio"]').change(function() {
            const labels = $(this).closest('.rating-stars').find('label');
            labels.removeClass('active');

            const rating = parseInt(this.value);
            for (let i = 1; i <= rating; i++) {
                $(`#star${i}`).next('label').addClass('active');
            }
        });
    });
    $(document).ready(function() {
        // Обработка добавления в корзину
        $('.add-to-cart').click(function() {
            <?php if ($category === 'lenses'): ?>
            const opticalPower = $('#optical_power').val();
            const baseCurve = $('#base_curve').val();

            if (!opticalPower || !baseCurve) {
                alert('Пожалуйста, выберите оптическую силу и кривизну');
                return;
            }

            // Для линз создаем уникальный идентификатор товара на основе параметров
            const uniqueProductId = `<?= $id ?>_${opticalPower}_${baseCurve}`;
            const productName = `<?= addslashes($product['name']) ?> (${opticalPower}, ${baseCurve})`;
            <?php else: ?>
            const uniqueProductId = <?= $id ?>;
            const productName = "<?= addslashes($product['name']) ?>";
            <?php endif; ?>

            const productPrice = "<?= $product['price'] ?>";
            const category = "<?= $category ?>";
            const quantity = 1;

            $.post('cart.php', {
                add_to_cart: true,
                item_id: uniqueProductId,
                original_item_id: <?= $id ?>, // Сохраняем оригинальный ID для ссылок
                item_name: productName,
                item_price: productPrice,
                category: category,
                quantity: quantity,
                <?php if ($category === 'lenses'): ?>
                optical_power: opticalPower,
                base_curve: baseCurve,
                <?php endif; ?>
            }, function(response) {
                if (response.success) {
                    alert(response.message);
                    if (response.cart_count !== undefined) {
                        $('.cart-count').text(response.cart_count).show();
                    }
                } else {
                    alert(response.message);
                }
            }, 'json').fail(function() {
                alert('Ошибка при добавлении в корзину');
            });
        });


   // Обработка добавления/удаления из избранного
    $('.favorite-btn:not(.disabled)').click(function(e) {
        e.preventDefault();

        const btn = $(this);
        const productId = btn.data('product-id');
        const productCategory = btn.data('product-category');

        // Если товар уже в избранном, показываем подтверждение удаления
        if (btn.hasClass('active')) {
            if (!confirm('Удалить из избранного?')) {
                return;
            }
        }

        $.ajax({
            url: 'toggle_favorite.php',
            method: 'POST',
            dataType: 'json',
            data: {
                item_id: productId,
                item_type: productCategory
            },
            success: function(response) {
                if (response.success) {
                    if (response.isFavorite) {
                        btn.addClass('active');
                        alert('Товар добавлен в избранное');
                    } else {
                        btn.removeClass('active');
                        alert('Товар удален из избранного');
                    }
                    // Обновляем счетчик в шапке
                    $('.favorites-count').text(response.count).show();
                } else {
                    alert(response.message || 'Произошла ошибка');
                }
            },
            error: function() {
                alert('Ошибка соединения с сервером');
            }
        });
    });
    });

</script>
</body>
</html>
<?php include 'footer.php'; ?>