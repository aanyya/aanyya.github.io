<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Обновление количества товаров
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $category = $_POST['product_category'];
    $quantity = intval($_POST['quantity']);

    // Проверка на отрицательное количество
    if ($quantity < 0) {
        $_SESSION['error'] = "Количество не может быть отрицательным";
        header("Location: adminka_4.php");
        exit();
    }

    // Определяем таблицу по категории
    $table_map = [
        'lenses' => 'items_lenses',
        'sun' => 'items_sun',
        'vision' => 'items_vision',
        'computer' => 'items_computer',
        'image' => 'items_image'
    ];

    if (!array_key_exists($category, $table_map)) {
        $_SESSION['error'] = "Неверная категория товара";
        header("Location: adminka_4.php");
        exit();
    }

    $table = $table_map[$category];

    try {
        $stmt = $pdo->prepare("UPDATE $table SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);

        $_SESSION['success'] = "Количество товара успешно обновлено";
        header("Location: adminka_4.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при обновлении количества: " . $e->getMessage();
        header("Location: adminka_4.php");
        exit();
    }
}

// Фильтрация и поиск
$filter_category = $_GET['category'] ?? 'all';
$search_query = $_GET['search'] ?? '';
$stock_filter = $_GET['stock'] ?? 'all';

try {
    // Собираем запросы для каждой таблицы отдельно
    $queries = [];
    $params = [];

    $tables = [
        'lenses' => 'items_lenses',
        'sun' => 'items_sun',
        'vision' => 'items_vision',
        'computer' => 'items_computer',
        'image' => 'items_image'
    ];

    foreach ($tables as $category => $table) {
        $where = [];
        $current_params = [];

        // Фильтр по категории
        if ($filter_category != 'all' && $filter_category == $category) {
            $where[] = "1"; // Просто условие для корректного объединения
        } elseif ($filter_category != 'all') {
            continue; // Пропускаем другие категории, если выбрана конкретная
        }

        // Поиск по названию
        if (!empty($search_query)) {
            $where[] = "name LIKE ?";
            $current_params[] = "%$search_query%";
        }

        // Фильтр по наличию
        if ($stock_filter == 'in_stock') {
            $where[] = "quantity > 0";
        } elseif ($stock_filter == 'out_of_stock') {
            $where[] = "quantity <= 0";
        }

        $where_clause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $queries[] = [
            'sql' => "SELECT '$category' AS category, id, name, CAST(price AS DECIMAL(10,2)) AS price, quantity FROM $table $where_clause",
            'params' => $current_params
        ];
    }

    // Объединяем все запросы
    $sql = "";
    $all_params = [];
    foreach ($queries as $i => $query) {
        if ($i > 0) {
            $sql .= " UNION ALL ";
        }
        $sql .= $query['sql'];
        $all_params = array_merge($all_params, $query['params']);
    }

    $sql .= " ORDER BY name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($all_params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка при выполнении запроса: " . $e->getMessage());
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
    <title>luw – всё о лучших очках</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="img/favicon.png" />
    <style>
        :root {
            --bg-dark: #121212;
            --bg-light: #1E1E1E;
            --text-light: #F5F5F5;
            --text-muted: #B0B0B0;
            --accent-brown: #8B5A2B;
            --accent-light: #D4A76A;
            --border-color: #2E2E2E;
            --success-color: #81C784;
            --danger-color: #E57373;
            --info-color: #64B5F6;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background: var(--bg-light);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-top: 110px;
            border: 1px solid var(--border-color);
        }

        h1, h2, h3 {
            color: var(--text-light);
            font-weight: 500;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .admin-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-nav a {
            padding: 10px 18px;
            border-radius: 6px;
            background-color: transparent;
            color: var(--accent-brown);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid var(--accent-brown);
        }

        .admin-nav a:hover {
            background-color: var(--accent-brown);
            color: var(--text-light);
            transform: translateY(-2px);
        }

        .products-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-item {
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: #2A2A2A;
            transition: all 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--accent-light);
        }

        .product-category {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .product-price {
            margin-bottom: 10px;
            color: var(--text-light);
        }

        .quantity-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .quantity-input {
            width: 80px;
            padding: 8px;
            text-align: center;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 4px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: transparent;
            color: var(--info-color);
            border: 1px solid var(--info-color);
        }

        .btn-primary:hover {
            background-color: var(--info-color);
            color: var(--text-light);
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

        .filter-group label {
            margin-bottom: 5px;
            color: var(--text-muted);
            font-size: 14px;
        }

        select {
            padding: 8px 15px;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 6px;
            cursor: pointer;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-input {
            padding: 10px 15px;
            width: 300px;
            max-width: 100%;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 6px;
        }

        .search-btn {
            padding: 10px 20px;
            background-color: transparent;
            color: var(--accent-brown);
            border: 1px solid var(--accent-brown);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }

        .search-btn:hover {
            background-color: var(--accent-brown);
            color: var(--text-light);
        }

        .out-of-stock {
            color: var(--danger-color);
            font-weight: 500;
        }

        .in-stock {
            color: var(--success-color);
            font-weight: 500;
        }

        .no-products {
            margin: 25px 0;
            padding: 20px;
            background-color: #2A2A2A;
            border-radius: 8px;
            text-align: center;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
            grid-column: 1 / -1;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(129, 199, 132, 0.2);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        .alert-error {
            background-color: rgba(229, 115, 115, 0.2);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }
                .stock-filter {
            margin-left: 15px;
        }

        /* Мобильная версия */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
            }

            .products-list {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .filters {
                flex-direction: column;
            }

            .search-box {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .search-input {
                width: 100%;
            }

            .search-btn {
                margin-left: 0;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .products-list {
                grid-template-columns: 1fr;
            }

            .admin-nav {
                flex-direction: column;
                align-items: center;
            }

            .admin-nav a {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Управление складом</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

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
                <select name="category" onchange="updateFilters()">
                    <option value="all" <?= $filter_category == 'all' ? 'selected' : '' ?>>Все товары</option>
                    <option value="vision" <?= $filter_category == 'vision' ? 'selected' : '' ?>>Очки для зрения</option>
                    <option value="sun" <?= $filter_category == 'sun' ? 'selected' : '' ?>>Солнцезащитные очки</option>
                    <option value="lenses" <?= $filter_category == 'lenses' ? 'selected' : '' ?>>Контактные линзы</option>
                    <option value="computer" <?= $filter_category == 'computer' ? 'selected' : '' ?>>Компьютерные очки</option>
                    <option value="image" <?= $filter_category == 'image' ? 'selected' : '' ?>>Имиджевые очки</option>
                </select>
            </div>

            <div class="filter-group stock-filter">
                <label>Наличие</label>
                <select name="stock" onchange="updateFilters()">
                    <option value="all" <?= $stock_filter == 'all' ? 'selected' : '' ?>>Все</option>
                    <option value="in_stock" <?= $stock_filter == 'in_stock' ? 'selected' : '' ?>>В наличии</option>
                    <option value="out_of_stock" <?= $stock_filter == 'out_of_stock' ? 'selected' : '' ?>>Нет в наличии</option>
                </select>
            </div>
        </div>
        <div class="products-list">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                    <div class="product-item">
                        <div class="product-name"><?= htmlspecialchars($row['name']) ?></div>
                        <div class="product-category">Категория: <?= htmlspecialchars($row['category']) ?></div>
                        <div class="product-price">Цена: <?= htmlspecialchars($row['price']) ?> ₽</div>
                        <div class="<?= $row['quantity'] <= 0 ? 'out-of-stock' : 'in-stock' ?>">
                            На складе: <?= $row['quantity'] <= 0 ? 'Нет в наличии' : $row['quantity'] ?>
                        </div>
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="product_category" value="<?= $row['category'] ?>">
                            <input type="number" name="quantity" class="quantity-input" value="<?= $row['quantity'] ?>" min="0">
                            <button type="submit" name="update_quantity" class="btn btn-primary">Обновить</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>Товары не найдены</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
        <script>
        function updateFilters() {
            const category = document.querySelector('select[name="category"]').value;
            const stock = document.querySelector('select[name="stock"]').value;
            const search = '<?= htmlspecialchars($search_query) ?>';

            let url = `adminka_4.php?category=${category}&stock=${stock}`;
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }

            window.location.href = url;
        }
    </script>
</body>
</html>