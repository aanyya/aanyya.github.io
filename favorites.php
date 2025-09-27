<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: vhod.php");
    exit();
}

$userId = $_SESSION['user_id'];

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
            i.rating,
            i.popularity,
            i.structure
        FROM favorites f
        LEFT JOIN (
            SELECT id, name, price, image, quantity, type, color, shape, rating, popularity, structure, 'lenses' as category FROM items_lenses
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, popularity, structure, 'sun' FROM items_sun
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, popularity, structure, 'vision' FROM items_vision
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, popularity, structure, 'image' FROM items_image
            UNION ALL SELECT id, name, price, image, quantity, type, color, shape, rating, popularity, structure, 'computer' FROM items_computer
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
        return null;
    } catch (PDOException $e) {
        error_log("Ошибка при получении рейтинга товара: " . $e->getMessage());
        return null;
    }
}
// Обработка удаления из избранного
if (isset($_GET['remove_favorite'])) {
    $productId = intval($_GET['item_id']);
    $productCategory = $_GET['item_type'];

    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND product_category = ?");
        $stmt->execute([$userId, $productId, $productCategory]);

        // Обновляем счетчик в сессии
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
        $stmt->execute([$userId]);
        $_SESSION['favorites_count'] = $stmt->fetchColumn();

        // Перенаправляем без параметров, чтобы избежать повторного удаления при обновлении
        header("Location: favorites.php");
        exit();
    } catch (PDOException $e) {
        die("Ошибка при удалении из избранного: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Каталог стильных очков и оправ по выгодной цене в Москве">
    <title>luw – Избранное</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="img/favicon.png" />
    <style>
       .block-collection{
        padding-top: 100px;
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


        .product-rating.no-rating {
            color: #999;
            font-size: 0.9em;
        }

        .empty-favorites {
            text-align: center;
            padding: 40px;
            color: #666;
            border: 1px dashed #ddd;
            border-radius: 8px;
            margin: 30px 0;
        }

        .empty-favorites i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #A95F1F;
        }

        .empty-favorites h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-favorites p {
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #A95F1F;
            color: white;
            border: 1px solid #A95F1F;
        }

        .btn-primary:hover {
            background-color: #8a4e1a;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 1024px) {
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
    <h1 class="gradient">Избранное</h1>
</div>

<div class="catalog-container">
    <div class="products-container">
        <div class="products-grid">
            <?php if (empty($fullFavorites)): ?>
                <div class="empty-favorites">
                    <i class="fas fa-heart"></i>
                    <h3>Ваше избранное пусто</h3>
                    <p>Добавляйте товары в избранное, чтобы легко найти их позже</p>
                    <div style="margin-top: 30px;">
                        <a href="collection_glasses.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Вернуться к покупкам
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($fullFavorites as $product):
                    $isOutOfStock = $product['quantity'] <= 0;
                    $productRating = getProductRating($pdo, $userId, $product['id'], $product['category']);
                ?>
                    <div class="product-card <?= $isOutOfStock ? 'out-of-stock' : '' ?>">
                        <?php if ($isOutOfStock): ?>
                            <div class="stock-label">Нет в наличии</div>
                        <?php endif; ?>

<a href="?remove_favorite=1&item_id=<?= $product['id'] ?>&item_type=<?= $product['category'] ?>"
   class="favorite-btn active"
   title="Удалить из избранного">
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

                            <a href="product_detail.php?id=<?= $product['id'] ?>&category=<?= $product['category'] ?>" class="product-link">подробнее</a>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($product['quantity'] > 0): ?>
                                    <form class="add-to-cart-form" method="POST" action="cart.php">
                                        <input type="hidden" name="item_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="item_name" value="<?= htmlspecialchars($product['name']) ?>">
                                        <input type="hidden" name="item_price" value="<?= $product['price'] ?>">
                                        <input type="hidden" name="category" value="<?= $product['category'] ?>">
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

   // Обработка клика на кнопку избранного
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const link = this.href;

            // AJAX-запрос для удаления
            fetch(link)
                .then(response => {
                    if (response.ok) {
                        // Находим родительскую карточку и удаляем ее
                        const card = this.closest('.product-card');
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => card.remove(), 300);

                        // Обновляем счетчик в шапке
                        const favCount = document.querySelector('.favorites-count');
                        if (favCount) {
                            const currentCount = parseInt(favCount.textContent);
                            favCount.textContent = currentCount - 1;
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Функция для подтверждения действия с избранным
    function confirmFavoriteAction(link, event) {
        event.preventDefault();

        // Подтверждение для удаления
        if (link.classList.contains('active')) {
            if (!confirm('Удалить из избранного?')) {
                return false;
            }
        }

        window.location.href = link.href;
        return false;
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