<?php
session_start();
require_once 'config.php';

// Проверка авторизации администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// Определяем фильтр (по умолчанию - ожидают модерации)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

// Получение отзывов с учетом фильтра
$query = "SELECT r.*, u.username, u.email
          FROM product_reviews r
          JOIN users u ON r.user_id = u.id";

if ($filter !== 'all') {
    $query .= " WHERE r.status = :status";
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);

if ($filter !== 'all') {
    $stmt->bindParam(':status', $filter, PDO::PARAM_STR);
}

$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка действий модератора
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['review_id'])) {
    $action = $_POST['action'];
    $review_id = (int)$_POST['review_id'];

    try {
        if ($action === 'approve') {
            $status = 'approved';
        } elseif ($action === 'reject') {
            $status = 'rejected';
        } else {
            throw new Exception("Неверное действие");
        }

        $updateStmt = $pdo->prepare("UPDATE product_reviews
                                    SET status = :status,
                                        moderated_at = NOW(),
                                        moderator_id = :moderator_id
                                    WHERE id = :review_id");

        $updateStmt->execute([
            ':status' => $status,
            ':moderator_id' => $_SESSION['user_id'],
            ':review_id' => $review_id
        ]);

        $_SESSION['success'] = "Отзыв успешно обработан";
        header("Location: admin_reviews.php?filter=$filter");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Ошибка: " . $e->getMessage();
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
            --danger-color: #E57373;
            --warning-color: #FFB74D;
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

        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 6px;
            background-color: transparent;
            color: var(--text-light);
            text-decoration: none;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .filter-tab:hover, .filter-tab.active {
            background-color: var(--accent-brown);
            color: var(--text-light);
            border-color: var(--accent-brown);
        }

        .filter-tab.pending { border-left: 4px solid var(--warning-color); }
        .filter-tab.approved { border-left: 4px solid var(--success-color); }
        .filter-tab.rejected { border-left: 4px solid var(--danger-color); }
        .filter-tab.all { border-left: 4px solid var(--info-color); }

        .table {
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

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-success {
            background-color: transparent;
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .btn-success:hover {
            background-color: var(--success-color);
            color: var(--text-light);
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

        .rating {
            color: var(--warning-color);
            font-size: 18px;
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

        .no-reviews {
            margin: 25px 0;
            padding: 20px;
            background-color: #2A2A2A;
            border-radius: 8px;
            text-align: center;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
        }

        /* Мобильная версия */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
            }

            .table {
                display: block;
                overflow-x: auto;
            }

            .filter-tabs {
                flex-direction: column;
                align-items: center;
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

            .table {
                display: block;
                width: 100%;
            }

            .table thead {
                display: none;
            }

            .table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 10px;
            }

            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px;
                border: none;
                border-bottom: 1px solid var(--border-color);
            }

            .table td:before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 10px;
                color: var(--accent-light);
            }

            .actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Управление отзывами</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="admin-nav">
            <a href="adminka_1.php">Управление товарами</a>
            <a href="adminka_2.php">Управление заказами</a>
            <a href="adminka_3.php">Управление аккаунтами</a>
            <a href="adminka_4.php">Управление складом</a>
            <a href="admin_reviews.php">Управление отзывами</a>
        </div>

        <div class="filter-tabs">
            <a href="?filter=pending" class="filter-tab pending <?= $filter === 'pending' ? 'active' : '' ?>">Ожидают модерации</a>
            <a href="?filter=approved" class="filter-tab approved <?= $filter === 'approved' ? 'active' : '' ?>">Одобренные</a>
            <a href="?filter=rejected" class="filter-tab rejected <?= $filter === 'rejected' ? 'active' : '' ?>">Отклоненные</a>
            <a href="?filter=all" class="filter-tab all <?= $filter === 'all' ? 'active' : '' ?>">Все отзывы</a>
        </div>

        <?php if (empty($reviews)): ?>
            <div class="no-reviews">
                <p>Нет отзывов для отображения</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Товар</th>
                        <th>Рейтинг</th>
                        <th>Текст отзыва</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td data-label="ID"><?= $review['id'] ?></td>
                        <td data-label="Пользователь">
                            <?= htmlspecialchars($review['username']) ?>
                            <br>
                            <small style="color: var(--text-muted)"><?= htmlspecialchars($review['email']) ?></small>
                        </td>
                        <td data-label="Товар"><?= $review['product_id'] ?> (<?= $review['product_category'] ?>)</td>
                        <td data-label="Рейтинг" class="rating"><?= str_repeat('★', $review['rating']) ?></td>
                        <td data-label="Текст отзыва"><?= htmlspecialchars($review['review_text']) ?></td>
                        <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                        <td data-label="Статус">
                            <?php
                                $status_class = '';
                                if ($review['status'] === 'approved') $status_class = 'status-approved';
                                elseif ($review['status'] === 'rejected') $status_class = 'status-rejected';
                                elseif ($review['status'] === 'pending') $status_class = 'status-pending';
                            ?>
                            <span class="<?= $status_class ?>"><?= $review['status'] ?></span>
                        </td>
                        <td data-label="Действия" class="actions">
                            <?php if ($review['status'] === 'pending'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success">Одобрить</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger">Отклонить</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--text-muted)">Обработан</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>