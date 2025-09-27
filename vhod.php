<?php
session_start();



// Подключаем конфигурацию базы данных
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Пожалуйста, заполните все поля.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
            $stmt->execute([$email, $password]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['logged_in'] = true;

                // Перенаправление после входа
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect");
                    exit();
                } else {
                    header("Location: kabinet.php");
                    exit();
                }
            } else {
                $message = "Неверный email или пароль.";
            }
        } catch (PDOException $e) {
            $message = "Ошибка при входе в систему: " . $e->getMessage();
        }
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
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background-color: #0e0e0e;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            margin-top: 200px;
            margin-bottom: 100px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 5px;
            display: block;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"],
        input[type="submit"] {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #b5e253;
            outline: none;
        }

        input[type="submit"] {
            background-color: #b5e253;
            color: black;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0e0e0e;
            color: #b5e253;
        }

        .error-message {
            color: red;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Вход в аккаунт</h2>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" name="login_submit" value="Войти">
        </form>

        <?php if (!empty($message)) { ?>
            <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <div class="register-link">
            <h3>Не зарегистрированы?<br>
            <a href="registr.php">Зарегистрироваться</a></h3>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>