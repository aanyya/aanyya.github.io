<?php
session_start();

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

// Подключаем конфигурацию базы данных
require_once 'config.php';

// Обработка удаления пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];

    try {
        // Проверяем существование пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            // Удаляем пользователя
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            // Перезагружаем страницу для обновления списка
            echo "<script>window.location.reload();</script>";
        } else {
            echo "<script>alert('Пользователь не найден');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Ошибка при удалении аккаунта: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Получаем список пользователей
try {
    $stmt = $pdo->query("SELECT id, username, email, is_admin FROM users");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка при получении списка пользователей: " . $e->getMessage());
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

        .no-users {
            margin: 25px 0;
            padding: 20px;
            background-color: #2A2A2A;
            border-radius: 8px;
            text-align: center;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
        }

        .admin-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .admin-yes {
            background-color: var(--success-color);
            color: #000;
        }

        .admin-no {
            background-color: var(--bg-dark);
            color: var(--text-light);
            border: 1px solid var(--border-color);
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
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Управление аккаунтами</h1>

        <div class="admin-nav">
            <a href="adminka_1.php">Управление товарами</a>
            <a href="adminka_2.php">Управление заказами</a>
            <a href="adminka_3.php">Управление аккаунтами</a>
            <a href="adminka_4.php">Управление складом</a>
            <a href="admin_reviews.php">Управление отзывами</a>
        </div>

        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя пользователя</th>
                        <th>Email</th>
                        <th>Администратор</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="ID"><?= htmlspecialchars($user['id']) ?></td>
                            <td data-label="Имя пользователя"><?= htmlspecialchars($user['username']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                            <td data-label="Администратор">
                                <span class="admin-badge <?= $user['is_admin'] ? 'admin-yes' : 'admin-no' ?>">
                                    <?= $user['is_admin'] ? 'Да' : 'Нет' ?>
                                </span>
                            </td>
                            <td data-label="Действия">
                                <form method='POST' action=''>
                                    <input type='hidden' name='user_id' value='<?= $user['id'] ?>'>
                                    <button type='submit' name='delete_user' class='btn btn-danger' onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-users">
                <p>Нет аккаунтов для отображения</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>