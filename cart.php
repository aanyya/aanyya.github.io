<?php
session_start();
require_once 'config.php';

// Используем PDO для всех запросов
try {
    $pdo = new PDO("mysql:host=localhost;dbname=optika", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

if (isset($_POST['add_to_cart'])) {
    $item_id = intval($_POST['item_id']);
    $item_name = $_POST['item_name'];
    $item_price = floatval($_POST['item_price']);
    $quantity = intval($_POST['quantity']);
    $category = $_POST['category'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = [
            'name' => $item_name,
            'price' => $item_price,
            'quantity' => $quantity,
            'category' => $category
        ];
    }

    header("Location: cart.php");
    exit();
}

if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    $updatedItems = [];
    foreach ($_POST['quantity'] as $id => $qty) {
        $id = intval($id);
        $qty = intval($qty);

        // Получаем информацию о товаре из корзины
        $item = $_SESSION['cart'][$id];

        // Проверяем доступное количество на складе
        $stmt = $pdo->prepare("SELECT quantity FROM items_{$item['category']} WHERE id = ?");
        $stmt->execute([$id]);
        $availableQuantity = $stmt->fetchColumn();

        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } elseif ($qty > $availableQuantity) {
            $qty = $availableQuantity;
            $_SESSION['cart'][$id]['quantity'] = $qty;
            $updatedItems[$id] = $availableQuantity;
        } else {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }

    // Возвращаем обновленную сумму
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    echo json_encode([
        'total' => $total,
        'updatedItems' => $updatedItems
    ]);
    exit();
}

if (isset($_POST['checkout'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Для оформления заказа необходимо авторизоваться";
        header("Location: vhod.php");
        exit();
    }

    // Простая валидация
    $required = ['phone', 'city', 'street', 'house'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Заполните все обязательные поля";
            header("Location: cart.php");
            exit();
        }
    }

    try {
        $pdo->beginTransaction();

        // Проверка доступности товаров перед оформлением
        foreach ($_SESSION['cart'] as $id => $item) {
            $stmt = $pdo->prepare("SELECT quantity FROM items_{$item['category']} WHERE id = ?");
            $stmt->execute([$id]);
            $available = $stmt->fetchColumn();

            if ($available < $item['quantity']) {
                $_SESSION['error'] = "Товар '{$item['name']}' доступен в количестве только {$available} шт.";
                header("Location: cart.php");
                $pdo->rollBack();
                exit();
            }
        }

        // 1. Создаем заказ
        $stmt = $pdo->prepare("INSERT INTO orders
            (user_id, total_price, phone, city, street, house, apartment, comment, status, order_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

        $total = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $_SESSION['cart']));

        $phone = '+7' . preg_replace('/[^0-9]/', '', $_POST['phone']);

        $stmt->execute([
            $_SESSION['user_id'],
            $total,
            $phone,
            $_POST['city'],
            $_POST['street'],
            $_POST['house'],
            $_POST['apartment'] ?? '',
            $_POST['comment'] ?? ''
        ]);

        $order_id = $pdo->lastInsertId();

        // 2. Добавляем товары
        $stmt_items = $pdo->prepare("INSERT INTO order_items
            (order_id, item_id, item_name, item_price, quantity, product_category)
            VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($_SESSION['cart'] as $id => $item) {
            $stmt_items->execute([
                $order_id,
                $id,
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['category']
            ]);

            // 3. Обновляем остатки
            $stmt_update = $pdo->prepare("UPDATE items_{$item['category']}
                SET quantity = quantity - ? WHERE id = ?");
            $stmt_update->execute([$item['quantity'], $id]);
        }

        $pdo->commit();

        // Очищаем корзину и перенаправляем
        unset($_SESSION['cart']);
        $_SESSION['order_success'] = $order_id;
        header("Location: order.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Ошибка оформления заказа: " . $e->getMessage();
        header("Location: cart.php");
        exit();
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
            --accent-brown: #b5e253;
            --accent-light: #b5e253;
            --border-color: #2E2E2E;
            --error: #E57373;
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

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
            transition: background 0.3s;
        }

        .cart-item:hover {
            background-color: #2A2A2A;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 4px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 14px;
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

        .btn-danger {
            background: transparent;
            color: var(--error);
            border: 1px solid var(--error);
        }

        .btn-danger:hover {
            background: var(--error);
            color: var(--text-light);
        }

        .btn-success {
            background: var(--accent-brown);
            color: var(--text-light);
            border: 1px solid var(--accent-brown);
        }

        .btn-success:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-light);
            transition: border 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-brown);
        }

        .phone-input {
            display: flex;
            align-items: center;
        }

        .phone-prefix {
            padding: 12px;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 6px 0 0 6px;
            color: var(--text-muted);
        }

        .error {
            color: var(--error);
            font-size: 14px;
            padding: 10px;
            background: rgba(229, 115, 115, 0.1);
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            color: var(--success);
            font-size: 18px;
            font-weight: 500;
            padding: 15px;
            background: rgba(129, 199, 132, 0.1);
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success i {
            font-size: 24px;
        }

        .total-sum {
            text-align: right;
            margin: 30px 0;
            font-size: 1.5rem;
            color: var(--accent-light);
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            margin: 30px 0;
        }

@media (max-width: 992px) {
    .container {
        width: 95%;
        padding: 20px;
    }

    .cart-item {
        flex-wrap: wrap;
    }

    .cart-item > div {
        flex: 1 1 50%;
        margin-bottom: 10px;
    }

    .cart-item > div:last-child {
        text-align: right;
    }
}

/* Mobile styles */
@media (max-width: 576px) {
    .container {
        margin-top: 80px;
        padding: 15px;
    }

    h1 {
        font-size: 1.5rem;
    }

    .cart-item > div {
        flex: 1 1 100%;
        text-align: left !important;
    }

    .quantity-input {
        width: 100%;
    }

    .btn {
        width: 100%;
        margin-top: 10px;
    }

    .phone-input {
        flex-direction: column;
    }

    .phone-prefix {
        border-radius: 6px;
        border: 1px solid var(--border-color);
        margin-bottom: 5px;
    }
}
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Корзина</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['order_success'])): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i>
                <span>Заказ #<?= htmlspecialchars($_SESSION['order_success']) ?> успешно оформлен!</span>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="collection_glasses.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Вернуться к покупкам
                </a>
            </div>
            <?php unset($_SESSION['order_success']); ?>
        <?php elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <form id="cartForm">
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $id => $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <div style="flex: 2;">
                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                        </div>
                        <div style="flex: 1; text-align: center;">
                            <?= number_format($item['price'], 2) ?> ₽ ×
                        </div>
                        <div style="flex: 1;">
                            <input type="number" name="quantity[<?= $id ?>]"
                                   value="<?= $item['quantity'] ?>"
                                   min="1" class="quantity-input" data-id="<?= $id ?>">
                        </div>
                        <div style="flex: 0;">
<button type="button" class="btn btn-danger remove-item" data-id="<?= $id ?>">
    Удалить
</button>
                        </div>
                        <div style="flex: 1; text-align: right; font-size: 18px;">
                            <?= number_format($subtotal, 2) ?> ₽
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>

            <div class="total-sum">
                Итого: <span id="total"><?= number_format($total, 2) ?></span> ₽
            </div>

            <h2 style="margin-top: 40px;">Оформление заказа</h2>

            <form method="POST" id="orderForm" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Телефон*</label>
                    <div class="phone-input">
                        <span class="phone-prefix">+7</span>
                        <input type="tel" name="phone" class="form-control"
                               placeholder="9001234567" maxlength="10" required
                               pattern="[0-9]{10}" title="10 цифр без +7">
                    </div>
                </div>

                <div class="form-group">
                    <label>Город*</label>
                    <input type="text" name="city" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Улица*</label>
                    <input type="text" name="street" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Дом*</label>
                    <input type="text" name="house" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Квартира</label>
                    <input type="text" name="apartment" class="form-control">
                </div>

                <div class="form-group">
                    <label>Комментарий к заказу</label>
                    <textarea name="comment" class="form-control" rows="3"></textarea>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" name="checkout" class="btn btn-success" style="padding: 12px 30px;">
                        Оформить заказ
                    </button>
                </div>
            </form>

        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h3>Ваша корзина пуста</h3>
                <p>Начните покупки, чтобы добавить товары в корзину</p>
                <div style="margin-top: 30px;">
                    <a href="collection_glasses.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Вернуться к покупкам
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
// Изменение количества
$('.quantity-input').on('change', function() {
    const id = $(this).data('id');
    const qty = $(this).val();

    $.post('cart.php', { quantity: { [id]: qty } }, function(data) {
        $('#total').text(data.total.toFixed(2));

        if (data.updatedItems) {
            for (const [id, qty] of Object.entries(data.updatedItems)) {
                $(`.quantity-input[data-id="${id}"]`).val(qty);
                alert(`Доступно только ${qty} шт. этого товара`);
            }
        }
    }, 'json').fail(function() {
        alert('Произошла ошибка при обновлении количества');
    });
});

// Удаление товара
$('.remove-item').click(function() {
    if (!confirm('Вы уверены, что хотите удалить товар из корзины?')) return;

    const id = $(this).data('id');
    $.post('cart.php', { quantity: { [id]: 0 } }, function(data) {
        // Обновляем итоговую сумму
        $('#total').text(data.total.toFixed(2));
        // Перезагружаем страницу для обновления списка товаров
        location.reload();
    }, 'json').fail(function() {
        alert('Произошла ошибка при удалении товара');
    });
});

        // Валидация телефона
        $('#orderForm').on('submit', function() {
            const phone = $('input[name="phone"]').val();
            if (!/^\d{10}$/.test(phone)) {
                alert('Номер телефона должен содержать 10 цифр');
                return false;
            }
            return true;
        });
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>