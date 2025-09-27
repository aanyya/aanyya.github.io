<?php
session_start();
require_once 'config.php';

error_log("Add to cart request received: " . print_r($_POST, true));

// Убедитесь, что нет никакого вывода перед этим
ob_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Для добавления в корзину необходимо авторизоваться']);
    ob_end_flush();
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    try {
        // Проверка обязательных полей
        if (!isset($_POST['item_id'], $_POST['item_name'], $_POST['item_price'], $_POST['category'])) {
            throw new Exception('Не все необходимые данные переданы');
        }

        $item_id = intval($_POST['item_id']);
        $item_name = trim($_POST['item_name']);
        $item_price = floatval(str_replace([' ', 'р.', '₽'], '', $_POST['item_price']));
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $category = trim($_POST['category']);

        // Проверяем доступное количество
        $stmt = $pdo->prepare("SELECT quantity FROM items_{$category} WHERE id = ?");
        $stmt->execute([$item_id]);
        $available = $stmt->fetchColumn();

        if ($available < $quantity) {
            echo json_encode(['success' => false, 'message' => "Доступно только {$available} шт. этого товара"]);
            ob_end_flush();
            exit();
        }

        // Инициализируем корзину, если ее нет
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Проверяем, есть ли уже такой товар в корзине
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $item_id && $item['category'] == $category) {
                $new_quantity = $item['quantity'] + $quantity;
                if ($new_quantity > $available) {
                    $item['quantity'] = $available;
                    echo json_encode(['success' => false, 'message' => "Максимальное количество этого товара в корзине: {$available}"]);
                    ob_end_flush();
                    exit();
                }
                $item['quantity'] = $new_quantity;
                $found = true;
                break;
            }
        }

        // Если товара еще нет в корзине, добавляем его
        if (!$found) {
            // Получаем изображение товара
            $stmt = $pdo->prepare("SELECT image FROM items_{$category} WHERE id = ?");
            $stmt->execute([$item_id]);
            $image = $stmt->fetchColumn();

            $_SESSION['cart'][] = [
                'id' => $item_id,
                'name' => $item_name,
                'price' => $item_price,
                'quantity' => $quantity,
                'category' => $category,
                'image' => $image
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Товар успешно добавлен в корзину',
            'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
        ]);
        ob_end_flush();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        ob_end_flush();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
    ob_end_flush();
}
?>