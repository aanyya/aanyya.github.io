<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Подключаем конфигурацию базы данных
require_once 'config.php';

// Функция для получения структуры таблицы
function getTableColumns($pdo, $table) {
    $columns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM $table");
    while ($row = $stmt->fetch()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

// Добавление товара
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $category = $_POST['product_category'];
    $table = "items_" . $category;

    try {
        // Получаем структуру таблицы
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = [];
        while ($row = $stmt->fetch()) {
            $columns[$row['Field']] = $row;
        }

        // Подготавливаем данные
        $data = [
            'name' => $_POST['product_name'],
            'price' => $_POST['product_price'],
            'image' => $_POST['product_image'],
            'type' => $_POST['product_type'],
            'color' => $_POST['product_color'],
            'shape' => $_POST['product_shape'] ?? NULL,
            'structure' => $_POST['product_structure'] ?? 'Не указано',
            'rating' => 4.5,
            'popularity' => 0,
            'quantity' => 10,
            'category' => $category
        ];

        // Формируем запрос только с существующими полями
        $fields = [];
        $values = [];
        $placeholders = [];

        foreach ($data as $field => $value) {
            if (array_key_exists($field, $columns)) {
                $fields[] = $field;
                $values[] = $value;
                $placeholders[] = '?';
            }
        }

        $query = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);

        header("Location: adminka_1.php?category=$category");
        exit();
    } catch (PDOException $e) {
        die("Ошибка при добавлении товара: " . $e->getMessage());
    }
}


// Удаление товара
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $category = $_GET['category'];
    $table = "items_" . $category;

    try {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$product_id]);

        header("Location: adminka_1.php?category=$category");
        exit();
    } catch (PDOException $e) {
        die("Ошибка при удалении товара: " . $e->getMessage());
    }
}

// Получение товаров
$filter_category = $_GET['category'] ?? 'all';
$products = [];

try {
    if ($filter_category == 'all') {
        $tables = ['items_vision', 'items_sun', 'items_lenses', 'items_computer', 'items_image'];
        foreach ($tables as $table) {
            $category = str_replace('items_', '', $table);
            $stmt = $pdo->query("SELECT *, '$category' as category FROM $table");
            while ($row = $stmt->fetch()) {
                $products[] = $row;
            }
        }
    } else {
        $table = "items_" . $filter_category;
        $stmt = $pdo->query("SELECT *, '$filter_category' as category FROM $table");
        $products = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Ошибка при получении товаров: " . $e->getMessage());
}

// Редактирование товара
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $category = $_POST['product_category'];
    $table = "items_" . $category;

    try {
        $data = [
            'name' => $_POST['product_name'],
            'price' => $_POST['product_price'],
            'image' => $_POST['product_image'],
            'type' => $_POST['product_type'],
            'color' => $_POST['product_color'],
            'shape' => $_POST['product_shape'],
            'structure' => $_POST['product_structure'] ?? 'Не указано',
            'id' => $product_id
        ];

        $query = "UPDATE $table SET
            name = :name,
            price = :price,
            image = :image,
            type = :type,
            color = :color,
            shape = :shape,
            structure = :structure
            WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->execute($data);

        header("Location: adminka_1.php?category=$category");
        exit();
    } catch (PDOException $e) {
        die("Ошибка при редактировании товара: " . $e->getMessage());
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
            --bg-dark: #121212;
            --bg-light: #1E1E1E;
            --text-light: #F5F5F5;
            --text-muted: #B0B0B0;
            --accent-brown: #8B5A2B;
            --accent-light: #D4A76A;
            --border-color: #2E2E2E;
            --success-color: #81C784;
            --warning-color: #FFB74D;
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

        h2 {
            font-size: 1.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--accent-light);
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

        .container-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="number"], select, textarea {
            display: block;
            margin: 8px 0;
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: #2A2A2A;
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent-brown);
        }

        input[type="submit"] {
            background-color: var(--accent-brown);
            color: var(--text-light);
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            transition: all 0.3s;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: var(--accent-light);
            transform: translateY(-2px);
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            background-color: #2A2A2A;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }

        li:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .but_1 {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            background-color: var(--accent-brown);
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .but_1:hover {
            background-color: var(--accent-light);
            transform: translateY(-2px);
        }

        .but_2 {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            background-color: transparent;
            color: var(--danger-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid var(--danger-color);
            cursor: pointer;
            margin-right: 10px;
        }

        .but_2:hover {
            background-color: var(--danger-color);
            color: var(--text-light);
            transform: translateY(-2px);
        }

        .but_3 {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            background-color: transparent;
            color: var(--info-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid var(--info-color);
            cursor: pointer;
        }

        .but_3:hover {
            background-color: var(--info-color);
            color: var(--text-light);
            transform: translateY(-2px);
        }

        img {
            border-radius: 4px;
            margin-left: 10px;
            vertical-align: middle;
        }

        form[style="display: inline;"] {
            margin-top: 15px;
        }

        /* Мобильная версия */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
                margin-top: 90px;
            }

            li {
                padding: 15px;
            }

            .container-categories {
                justify-content: center;
            }

            .but_1, .but_2, .but_3 {
                padding: 6px 12px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .admin-nav {
                flex-direction: column;
                align-items: center;
            }

            .admin-nav a {
                width: 100%;
                text-align: center;
            }

            input[type="text"], input[type="number"], select, textarea {
                width: 100%;
            }

            form[style="display: inline;"] {
                display: block;
                margin-top: 10px;
            }

            .but_2, .but_3 {
                display: block;
                width: 100%;
                margin: 5px 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Управление товарами</h1>

    <div class="admin-nav">
        <a href="adminka_1.php">Управление товарами</a>
        <a href="adminka_2.php">Управление заказами</a>
        <a href="adminka_3.php">Управление аккаунтами</a>
        <a href="adminka_4.php">Управление складом</a>
        <a href="admin_reviews.php">Управление отзывами</a>
    </div>

    <h2>Фильтр по категориям</h2>
    <div class="container-categories">
        <a href="?category=all" class="but_1">Все</a>
        <a href="?category=vision" class="but_1">Очки для зрения</a>
        <a href="?category=sun" class="but_1">Солнцезащитные очки</a>
        <a href="?category=lenses" class="but_1">Контактные линзы</a>
        <a href="?category=computer" class="but_1">Компьютерные очки</a>
        <a href="?category=image" class="but_1">Имиджевые очки</a>
    </div>

    <h2>Добавить товар</h2>
    <form method="POST">
        <input type="text" name="product_name" required placeholder="Название товара">
        <input type="text" name="product_price" required placeholder="Цена" pattern="\d+(\.\d{2})?">
        <input type="text" name="product_image" required placeholder="Ссылка на изображение">
        <input type="text" name="product_type" required placeholder="Тип (Женские, Мужские)">
        <input type="text" name="product_color" required placeholder="Цвет">
        <input type="text" name="product_shape" placeholder="Форма (не для линз)">
        <input type="text" name="product_structure" placeholder="Описание (structure)" value="Не указано">
        <select name="product_category" required>
            <option value="vision">Очки для зрения</option>
            <option value="sun">Солнцезащитные очки</option>
            <option value="lenses">Контактные линзы</option>
            <option value="computer">Компьютерные очки</option>
            <option value="image">Имиджевые очки</option>
        </select>
        <input class="but_1" type="submit" name="add_product" value="Добавить товар">
    </form>

    <h2>Список товаров</h2>
    <ul>
        <?php foreach ($products as $row): ?>
<li>
    <strong><?= htmlspecialchars($row['name']) ?></strong> - <?= htmlspecialchars($row['price']) ?> руб.
    <br>Тип: <?= htmlspecialchars($row['type']) ?>
    <br>Цвет: <?= htmlspecialchars($row['color']) ?>
    <br>Форма: <?= htmlspecialchars($row['shape']) ?>
    <br>Описание: <?= htmlspecialchars($row['structure']) ?>
    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" style="width:50px;height:50px;">
    <a class="but_2" href="?delete_product=<?= $row['id'] ?>&category=<?= $row['category'] ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
    <form method="POST" style="display: inline;">
        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="product_category" value="<?= $row['category'] ?>">
        <input type="text" name="product_name" value="<?= htmlspecialchars($row['name']) ?>" required>
        <input type="number" step="0.01" name="product_price" value="<?= $row['price'] ?>" required>
        <input type="text" name="product_image" value="<?= htmlspecialchars($row['image']) ?>" required>
        <input type="text" name="product_type" value="<?= htmlspecialchars($row['type']) ?>" required>
        <input type="text" name="product_color" value="<?= htmlspecialchars($row['color']) ?>" required>
        <input type="text" name="product_shape" value="<?= htmlspecialchars($row['shape']) ?>" required>
        <input type="text" name="product_structure" value="<?= htmlspecialchars($row['structure']) ?>">
        <input class="but_3" type="submit" name="edit_product" value="Редактировать">
    </form>
</li>
        <?php endforeach; ?>
    </ul>
</div>
<?php include 'footer.php'; ?>
</body>
</html>