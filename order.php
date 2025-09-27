<?php
session_start();

if (!isset($_SESSION['order_success'])) {
    header("Location: cart.php");
    exit();
}

$order_id = $_SESSION['order_success'];
unset($_SESSION['order_success']);

require_once 'config.php';

try {
    // Получаем информацию о заказе
    $order_query = "SELECT o.*, u.username
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    WHERE o.id = ?";
    $stmt = $pdo->prepare($order_query);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        die("Заказ #$order_id не найден");
    }

    // Получаем товары в заказе
    $items_query = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt_items = $pdo->prepare($items_query);
    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll();

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
            --success: #81C784;
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

        .order-success {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(129, 199, 132, 0.1);
            border-radius: 8px;
            border: 1px solid var(--success);
        }

        .order-success i {
            font-size: 48px;
            color: var(--success);
            margin-bottom: 15px;
        }

        .order-success h1 {
            color: var(--success);
            border: none;
        }

        .order-details {
            margin: 30px 0;
            padding: 20px;
            background: #2A2A2A;
            border-radius: 8px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-color);
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            flex: 1;
            color: var(--accent-light);
            font-weight: 500;
        }

        .detail-value {
            flex: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
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
            color: var(--success);
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

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
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
            background-color: var(--accent-brown);
            color: var(--text-light);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .btn-group {
                flex-direction: column;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="order-success">
            <i class="fas fa-check-circle"></i>
            <h1>Заказ успешно оформлен!</h1>
            <p style="font-size: 1.2rem;">Номер вашего заказа: <strong>#<?= $order_id ?></strong></p>
        </div>

        <div class="order-details">
            <h2 style="margin-bottom: 20px; color: var(--accent-light);">Детали заказа</h2>

            <div class="detail-row">
                <div class="detail-label">Статус:</div>
                <div class="detail-value status-<?= $order['status'] ?>">
                    <?php
                    $statuses = [
                        'pending' => 'В обработке',
                        'approved' => 'Подтвержден',
                        'rejected' => 'Отклонен',
                        'completed' => 'Выполнен'
                    ];
                    echo $statuses[$order['status']] ?? $order['status'];
                    ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Дата:</div>
                <div class="detail-value"><?= date('d.m.Y H:i', strtotime($order['order_date'])) ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Сумма:</div>
                <div class="detail-value"><?= number_format($order['total_price'], 2) ?> ₽</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Адрес доставки:</div>
                <div class="detail-value">
                    <?= htmlspecialchars($order['city']) ?>,
                    <?= htmlspecialchars($order['street']) ?>,
                    д.<?= htmlspecialchars($order['house']) ?>
                    <?= !empty($order['apartment']) ? ', кв.' . htmlspecialchars($order['apartment']) : '' ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Телефон:</div>
                <div class="detail-value"><?= htmlspecialchars($order['phone']) ?></div>
            </div>

            <?php if (!empty($order['comment'])): ?>
            <div class="detail-row">
                <div class="detail-label">Комментарий:</div>
                <div class="detail-value"><?= htmlspecialchars($order['comment']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <h3 style="margin-top: 40px;">Состав заказа</h3>
        <table>
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= number_format($item['item_price'], 2) ?> ₽</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['item_price'] * $item['quantity'], 2) ?> ₽</td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Итого:</strong></td>
                    <td><strong><?= number_format($order['total_price'], 2) ?> ₽</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="btn-group">
            <a href="kabinet.php" class="btn btn-primary">
                <i class=""></i> Личный кабинет
            </a>
            <a href="collection_glasses.php" class="btn btn-primary">
                <i class=""></i> Продолжить покупки
            </a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>