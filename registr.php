<?php
session_start();

// Подключаем конфигурацию базы данных
require_once 'config.php';

// Обработка формы ДО любого вывода HTML
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $is_admin = 0;

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Все поля обязательны для заполнения.";
    }
    // 1. Проверка имени пользователя (только англ. буквы, цифры, тире и подчеркивания)
    elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $error = "Имя пользователя может содержать только английские буквы, цифры, тире и подчеркивания.";
    }
    // 3. Проверка, что имя не состоит только из цифр/символов
    elseif (preg_match('/^[0-9_-]+$/', $username)) {
        $error = "Имя не может состоять только из цифр, подчеркиваний и тире.";
    }
    // 3. Проверка, что email не состоит только из цифр/символов (до @)
    elseif (preg_match('/^[0-9_-]+@/', $email)) {
        $error = "Email не может состоять только из цифр, подчеркиваний и тире.";
    }
    // Проверка длины email
    elseif (strlen($email) < 5) {
        $error = "Email должен содержать не менее 5 символов.";
    }
    // Проверка формата email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный формат email.";
    }
    // Проверка длины пароля
    elseif (strlen($password) < 5) {
        $error = "Пароль должен содержать не менее 5 символов.";
    }
    // Проверка совпадения паролей
    elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают!";
    }
    else {
        try {
            // Проверяем, существует ли пользователь с таким email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Пользователь с таким email уже существует.";
            } else {
                // Создаем нового пользователя
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password, $is_admin]);

                // Получаем ID нового пользователя
                $user_id = $pdo->lastInsertId();

                // Сохраняем все данные пользователя в сессии
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['is_admin'] = $is_admin;
                $_SESSION['logged_in'] = true;

                // Перенаправляем в личный кабинет
                header("Location: kabinet.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Ошибка при регистрации: " . $e->getMessage();
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
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 200px;
            margin-bottom: 80px;

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

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="submit"] {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus,
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
        <h2>Зарегистрироваться</h2>
        <form method="POST" action="">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username"
                   required
                   pattern="[a-zA-Z0-9_-]+"
                   minlength="5"
                   title="Только английские буквы, цифры, тире и подчёркивания">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
                   required
                   minlength="5"
                   title="Email должен быть не менее 5 символов и содержать @">

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password"
                   required
                   minlength="5"
                   title="Пароль должен быть не менее 5 символов">

            <label for="confirm_password">Подтвердите пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   required
                   title="Повторите пароль">

            <input type="submit" name="register_submit" value="Зарегистрироваться">
        </form>

        <?php if(isset($error)): ?>
            <p class='error-message'><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="register-link">
            <h3>Уже зарегистрированы?<br>
            <a href="vhod.php">Войти в аккаунт</a></h3>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>