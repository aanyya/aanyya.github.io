<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Подключаем конфигурацию базы данных
require_once 'config.php';

// Обновление количества товаров
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_quantity'])) {
    $product_id = intval($_POST['product_id']);
    $product_category = $_POST['product_category'];
    $quantity = intval($_POST['quantity']);

    try {
        $table = "items_" . $product_category;
        $query = "UPDATE $table SET quantity = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$quantity, $product_id]);
    } catch (PDOException $e) {
        die("Ошибка при обновлении количества: " . $e->getMessage());
    }
}

//фильтрация
$filter_category = $_GET['category'] ?? 'all';
try {
    if ($filter_category == 'lenses') {
        $products = $pdo->query("SELECT 'lenses' AS category, id, name, price, structure AS description, image FROM items_lenses")->fetchAll();
    } elseif ($filter_category == 'sun') {
        $products = $pdo->query("SELECT 'sun' AS category, id, name, price, structure AS description, image FROM items_sun")->fetchAll();
    } elseif ($filter_category == 'vision') {
        $products = $pdo->query("SELECT 'vision' AS category, id, name, price, structure AS description, image FROM items_vision")->fetchAll();
    } else {
        $products = $pdo->query("
            SELECT 'lenses' AS category, id, name, price, structure AS description, image FROM items_lenses
            UNION ALL
            SELECT 'sun', id, name, price, structure AS description, image FROM items_sun
            UNION ALL
            SELECT 'vision', id, name, price, structure AS description, image FROM items_vision
        ")->fetchAll();
    }
} catch (PDOException $e) {
    die("Ошибка при загрузке товаров: " . $e->getMessage());
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
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background-color: #fff;
            color: #000;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            padding-top: 100px;
        }

        h1 {
            color: #A95F1F;
            margin-bottom: 20px;
            text-align: center;
        }

        .admin-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .admin-nav a {
            padding: 10px 15px;
            border-radius: 5px;
            background-color: #f8f4f0;
            color: #A95F1F;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover {
            background-color: #A95F1F;
            color: white;
        }

        h2 {
            color: #000;
        }

        .products-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-category {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .product-price {
            margin-bottom: 10px;
        }

        .quantity-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
        }

        .update-btn {
            background-color: #188DB5;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-input {
            padding: 8px 15px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-btn {
            padding: 8px 15px;
            background-color: #188DB5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .out-of-stock {
            color: #ff0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Управление складом</h1>

    <div class="admin-nav">
        <a href="adminka_1.php">Управление товарами</a>
        <a href="adminka_2.php">Управление заказами</a>
        <a href="adminka_3.php">Управление аккаунтами</a>
        <a href="adminka_4.php">Управление складом</a>
        <a href="admin_reviews.php">Управление отзывами</a>
    </div>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" class="search-input" placeholder="Поиск товаров..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="search-btn">Поиск</button>
        </form>
    </div>

    <div class="filters">
        <div class="filter-group">
            <label>Фильтр по категориям</label>
            <select name="category" onchange="location.href='?category='+this.value">
                <option value="all" <?= $filter_category == 'all' ? 'selected' : '' ?>>Все товары</option>
                <option value="vision" <?= $filter_category == 'vision' ? 'selected' : '' ?>>Очки для зрения</option>
                <option value="sun" <?= $filter_category == 'sun' ? 'selected' : '' ?>>Солнцезащитные очки</option>
                <option value="lenses" <?= $filter_category == 'lenses' ? 'selected' : '' ?>>Контактные линзы</option>
                <option value="computer" <?= $filter_category == 'computer' ? 'selected' : '' ?>>Компьютерные очки</option>
                <option value="image" <?= $filter_category == 'image' ? 'selected' : '' ?>>Имиджевые очки</option>
            </select>
        </div>
    </div>

    <div class="products-list">
        <?php foreach ($products as $row): ?>
            <div class="product-item">
                <div class="product-name"><?= htmlspecialchars($row['name']) ?></div>
                <div class="product-category">Категория: <?= htmlspecialchars($row['category']) ?></div>
                <div class="product-price">Цена: <?= htmlspecialchars($row['price']) ?> ₽</div>
                <div class="product-quantity <?= $row['quantity'] <= 0 ? 'out-of-stock' : '' ?>">
                    На складе: <?= $row['quantity'] <= 0 ? 'Нет в наличии' : $row['quantity'] ?>
                </div>
                <form method="POST" class="quantity-form">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="product_category" value="<?= $row['category'] ?>">
                    <input type="number" name="quantity" class="quantity-input" value="<?= $row['quantity'] ?>" min="0">
                    <button type="submit" name="update_quantity" class="update-btn">Обновить</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>