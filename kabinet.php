<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: vhod.php");
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];

try {
    // Получаем активные заказы (ожидание, подтвержден, отклонен)
    $active_orders_query = "SELECT
                o.*,
                (SELECT GROUP_CONCAT(CONCAT(oi.item_name, ' (', oi.quantity, ' шт.)|', oi.product_category, '|', oi.item_id) SEPARATOR '||')
                 FROM order_items oi
                 WHERE oi.order_id = o.id) as items
              FROM orders o
              WHERE o.user_id = ? AND o.status IN ('pending', 'approved', 'rejected')
              ORDER BY o.order_date DESC";

    $stmt_active = $pdo->prepare($active_orders_query);
    $stmt_active->execute([$user_id]);
    $active_orders = $stmt_active->fetchAll();

    // Получаем завершенные заказы
    $completed_orders_query = "SELECT
                o.*,
                (SELECT GROUP_CONCAT(CONCAT(oi.item_name, ' (', oi.quantity, ' шт.)|', oi.product_category, '|', oi.item_id) SEPARATOR '||')
                 FROM order_items oi
                 WHERE oi.order_id = o.id) as items
              FROM orders o
              WHERE o.user_id = ? AND o.status = 'completed'
              ORDER BY o.order_date DESC";

    $stmt_completed = $pdo->prepare($completed_orders_query);
    $stmt_completed->execute([$user_id]);
    $completed_orders = $stmt_completed->fetchAll();

    // Получаем данные пользователя
    $user_query = "SELECT * FROM users WHERE id = ?";
    $stmt_user = $pdo->prepare($user_query);
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();

} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
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

        .user-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .user-details {
            flex: 1;
        }

        .user-details h2 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--accent-light);
        }

        .user-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background-color: transparent;
            color: var(--accent-brown);
            border: 1px solid var(--accent-brown);
        }

        .btn-primary:hover {
            background-color: #b5e253;
            color: var(--text-light);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: transparent;
            color: #E57373;
            border: 1px solid #E57373;
            margin-top: 20px;
        }

        .btn-danger:hover {
            background: #E57373;
            color: var(--text-light);
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

        .status-pending {
            color: #FFB74D;
            font-weight: 500;
        }

        .status-approved {
            color: #81C784;
            font-weight: 500;
        }

        .status-rejected {
            color: #E57373;
            font-weight: 500;
        }

        .status-completed {
            color: #64B5F6;
            font-weight: 500;
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

        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 1.3rem;
            color: var(--accent-light);
            position: relative;
            padding-bottom: 10px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--accent-brown);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Личный кабинет</h1>
        <div class="user-info">
            <div class="user-details">
                <h2>Добро пожаловать, <?= htmlspecialchars($user['username']) ?>!</h2>
            </div>
            <div class="user-actions">
                <a href="collection_glasses.php" class="btn btn-primary">Вернуться к покупкам</a>
                <a href="my_reviews.php" class="btn btn-primary">Мои отзывы</a>
                <?php if ($user['is_admin']): ?>
                    <a href="adminka_1.php" class="btn btn-primary">Админ-панель</a>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="section-title">Активные заказы</h2>

<?php if (count($active_orders) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Номер заказа</th>
                <th>Дата</th>
                <th>Товары</th>
                <th>Сумма</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($active_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                    <td>
                        <?php
                        $items = explode('||', $order['items']);
                        foreach ($items as $item):
                            $parts = explode('|', $item);
                            if (count($parts) >= 3) {
                                $item_name = $parts[0];
                                $category = $parts[1];
                                $item_id = $parts[2];
                        ?>
                            <div>
                                <a href="product_detail.php?id=<?= $item_id ?>&category=<?= $category ?>" style="color: #D4A76A; text-decoration: none;">
                                    <?= htmlspecialchars($item_name) ?>
                                </a>
                            </div>
                        <?php
                            }
                        endforeach;
                        ?>
                    </td>
                    <td><?= number_format($order['total_price'], 2) ?> ₽</td>
                    <td class="status-<?= $order['status'] ?>">
                        <?php
                        $statuses = [
                            'pending' => 'В обработке',
                            'approved' => 'Подтвержден',
                            'rejected' => 'Отклонен',
                            'completed' => 'Выполнен'
                        ];
                        echo $statuses[$order['status']] ?? $order['status'];
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-orders">У вас нет активных заказов</div>
<?php endif; ?>

<h2 class="section-title">Завершенные заказы</h2>

<?php if (count($completed_orders) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Номер заказа</th>
                <th>Дата</th>
                <th>Товары</th>
                <th>Сумма</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($completed_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></td>
                    <td>
                        <?php
                        $items = explode('||', $order['items']);
                        foreach ($items as $item):
                            $parts = explode('|', $item);
                            if (count($parts) >= 3) {
                                $item_name = $parts[0];
                                $category = $parts[1];
                                $item_id = $parts[2];
                        ?>
                            <div>
                                <a href="product_detail.php?id=<?= $item_id ?>&category=<?= $category ?>" style="color: #D4A76A; text-decoration: none;">
                                    <?= htmlspecialchars($item_name) ?>
                                </a>
                            </div>
                        <?php
                            }
                        endforeach;
                        ?>
                    </td>
                    <td><?= number_format($order['total_price'], 2) ?> ₽</td>
                    <td class="status-completed">Выполнен</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-orders">У вас пока нет завершенных заказов</div>
<?php endif; ?>
        <a href="logout.php" class="btn btn-danger">Выйти</a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>