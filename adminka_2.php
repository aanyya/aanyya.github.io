<?php
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

// Подключаем конфигурацию базы данных
require_once 'config.php';

// Обработка изменения статуса
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
    } catch (PDOException $e) {
        die("Ошибка при обновлении статуса: " . $e->getMessage());
    }
}

// Обработка удаления заказа
if (isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);

    try {
        $pdo->beginTransaction();

        // Удаляем элементы заказа
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);

        // Удаляем сам заказ
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Ошибка при удалении: " . $e->getMessage());
    }
}

// Запрос для получения заказов
try {
    $query = "SELECT
                o.*,
                IFNULL(u.username, 'Гость') as username,
                GROUP_CONCAT(CONCAT(oi.item_name, ' (', oi.quantity, ' шт.)') SEPARATOR ', ') as items
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.id
              LEFT JOIN order_items oi ON o.id = oi.order_id
              GROUP BY o.id
              ORDER BY o.order_date DESC";

    $stmt = $pdo->query($query);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            background: var(--bg-light);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: #2A2A2A;
            color: var(--accent-light);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        tr:hover {
            background-color: #2A2A2A;
        }

        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-light);
            color: var(--text-light);
            cursor: pointer;
            transition: border-color 0.3s;
        }

        select:focus {
            outline: none;
            border-color: var(--accent-brown);
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-danger {
            background-color: transparent;
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        .btn-danger:hover {
            background-color: var(--danger-color);
            color: var(--text-light);
        }

        .no-orders {
            margin: 25px 0;
            padding: 20px;
            background-color: #2A2A2A;
            border-radius: 8px;
            text-align: center;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            color: var(--warning-color);
            font-weight: 500;
        }

        .status-approved {
            color: var(--success-color);
            font-weight: 500;
        }

        .status-rejected {
            color: var(--danger-color);
            font-weight: 500;
        }

        .status-completed {
            color: var(--info-color);
            font-weight: 500;
        }

        /* Мобильная версия */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
            }

            table {
                display: block;
                overflow-x: auto;
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

            table {
                display: block;
                width: 100%;
            }

            table thead {
                display: none;
            }

            table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 10px;
            }

            table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px;
                border: none;
                border-bottom: 1px solid var(--border-color);
            }

            table td:before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 10px;
                color: var(--accent-light);
            }

            .actions {
                display: flex;
                justify-content: center;
            }

            select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Управление заказами</h1>

        <div class="admin-nav">
            <a href="adminka_1.php">Управление товарами</a>
            <a href="adminka_2.php">Управление заказами</a>
            <a href="adminka_3.php">Управление аккаунтами</a>
            <a href="adminka_4.php">Управление складом</a>
            <a href="admin_reviews.php">Управление отзывами</a>
        </div>

        <?php if (!empty($orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Адрес</th>
                        <th>Товары</th>
                        <th>Сумма</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="ID"><?= $order['id'] ?></td>
                            <td data-label="Клиент"><?= htmlspecialchars($order['username']) ?></td>
                            <td data-label="Телефон"><?= htmlspecialchars($order['phone']) ?></td>
                            <td data-label="Адрес">
                                <?= htmlspecialchars($order['city']) ?>,
                                <?= htmlspecialchars($order['street']) ?>,
                                д.<?= htmlspecialchars($order['house']) ?>
                                <?= !empty($order['apartment']) ? ', кв.' . htmlspecialchars($order['apartment']) : '' ?>
                            </td>
                            <td data-label="Товары"><?= htmlspecialchars($order['items']) ?></td>
                            <td data-label="Сумма"><?= number_format($order['total_price'], 2) ?> ₽</td>
                            <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                            <td data-label="Статус">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>В обработке</option>
                                        <option value="approved" <?= $order['status'] == 'approved' ? 'selected' : '' ?>>Подтвержден</option>
                                        <option value="rejected" <?= $order['status'] == 'rejected' ? 'selected' : '' ?>>Отклонен</option>
                                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Выполнен</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="actions" data-label="Действия">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" class="btn btn-danger" name="delete_order" onclick="return confirm('Вы уверены, что хотите удалить этот заказ?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders">
                <p>Нет заказов для отображения</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>