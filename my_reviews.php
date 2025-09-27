<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: vhod.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Параметры фильтрации и сортировки
$search_query = $_GET['search'] ?? '';
$sort_order = $_GET['sort'] ?? 'newest';
$filter_rating = $_GET['rating'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';

try {
    // Базовый запрос для отзывов текущего пользователя
    $query = "SELECT pr.*, u.username,
              CASE
                WHEN pr.product_category = 'lenses' THEN il.name
                WHEN pr.product_category = 'sun' THEN isun.name
                WHEN pr.product_category = 'vision' THEN iv.name
                WHEN pr.product_category = 'image' THEN iimg.name
                WHEN pr.product_category = 'computer' THEN icomp.name
              END as product_name
              FROM product_reviews pr
              JOIN users u ON pr.user_id = u.id
              LEFT JOIN items_lenses il ON pr.product_category = 'lenses' AND pr.product_id = il.id
              LEFT JOIN items_sun isun ON pr.product_category = 'sun' AND pr.product_id = isun.id
              LEFT JOIN items_vision iv ON pr.product_category = 'vision' AND pr.product_id = iv.id
              LEFT JOIN items_image iimg ON pr.product_category = 'image' AND pr.product_id = iimg.id
              LEFT JOIN items_computer icomp ON pr.product_category = 'computer' AND pr.product_id = icomp.id
              WHERE pr.user_id = :user_id";

    $params = [':user_id' => $user_id];

    // Добавляем условия поиска
if (!empty($search_query)) {
    $query .= " AND (u.username LIKE :search_username OR
                    pr.review_text LIKE :search_text OR
                    il.name LIKE :search_lenses OR
                    isun.name LIKE :search_sun OR
                    iv.name LIKE :search_vision OR
                    iimg.name LIKE :search_image OR
                    icomp.name LIKE :search_computer)";
    $params[':search_username'] = "%$search_query%";
    $params[':search_text'] = "%$search_query%";
    $params[':search_lenses'] = "%$search_query%";
    $params[':search_sun'] = "%$search_query%";
    $params[':search_vision'] = "%$search_query%";
    $params[':search_image'] = "%$search_query%";
    $params[':search_computer'] = "%$search_query%";
}

    // Фильтр по рейтингу
    if ($filter_rating != 'all' && is_numeric($filter_rating)) {
        $query .= " AND pr.rating = :rating";
        $params[':rating'] = $filter_rating;
    }

    // Фильтр по статусу
    if ($filter_status != 'all') {
        $query .= " AND pr.status = :status";
        $params[':status'] = $filter_status;
    }

    // Сортировка
    switch ($sort_order) {
        case 'oldest':
            $query .= " ORDER BY pr.created_at ASC";
            break;
        case 'highest':
            $query .= " ORDER BY pr.rating DESC, pr.created_at DESC";
            break;
        case 'lowest':
            $query .= " ORDER BY pr.rating ASC, pr.created_at DESC";
            break;
        default: // 'newest'
            $query .= " ORDER BY pr.created_at DESC";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

// Функция для красивого отображения названий категорий
function displayCategoryName($category) {
    $names = [
        'lenses' => 'Линзы',
        'sun' => 'Солнцезащитные очки',
        'vision' => 'Очки для зрения',
        'image' => 'Имиджевые очки',
        'computer' => 'Компьютерные очки'
    ];
    return $names[$category] ?? ucfirst($category);
}

// Функция для отображения статуса отзыва
function displayStatus($status) {
    $statuses = [
        'pending' => 'На модерации',
        'approved' => 'Опубликован',
        'rejected' => 'Отклонён'
    ];
    return $statuses[$status] ?? ucfirst($status);
}

// Функция для цвета статуса
function getStatusColor($status) {
    $colors = [
        'pending' => '#FFA500', // оранжевый
        'approved' => '#28a745', // зеленый
        'rejected' => '#dc3545'  // красный
    ];
    return $colors[$status] ?? '#B0B0B0';
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
            --bg-lighter: #2A2A2A;
            --text-light: #F5F5F5;
            --text-muted: #B0B0B0;
            --accent-brown: #8B5A2B;
            --accent-light: #D4A76A;
            --border-color: #2E2E2E;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --gold: #FFD700;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background-color: var(--bg-dark);
            color: var(--text-light);
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

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent-light);
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--accent-brown);
        }

        .back-link i {
            margin-right: 8px;
        }

        .reviews-list {
            margin-top: 30px;
        }

        .review {
            margin-bottom: 25px;
            padding: 20px;
            background: var(--bg-lighter);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .review:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .review h3 {
            color: var(--accent-light);
            margin-top: 0;
            margin-bottom: 10px;
            padding-right: 30px;
        }

        .rating {
            color: var(--gold);
            font-size: 1.2rem;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .review-text {
            margin-bottom: 15px;
            line-height: 1.7;
            color: var(--text-light);
        }

        .review-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 10px;
        }

        .review-status {
            font-weight: 500;
        }

        .no-reviews {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
        }

        .delete-review {
            position: absolute;
            top: 15px;
            right: 15px;
            color: var(--danger-color);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s;
        }

        .delete-review:hover {
            color: #ff0000;
        }

        .messages {
            margin-bottom: 20px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: var(--danger-color);
        }

        /* Фильтры и поиск */
        .search-box {
            margin-bottom: 20px;
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .search-input, select {
            width: 100%;
            padding: 10px 15px;
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
        }

        .search-btn {
            padding: 10px 20px;
            background-color: transparent;
            color: var(--accent-light);
            border: 1px solid var(--accent-light);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            margin-top: 10px;
        }

        .search-btn:hover {
            background-color: var(--accent-light);
            color: var(--bg-dark);
        }

        /* Адаптация для мобильных и планшетов */
        @media (max-width: 992px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            .review-meta {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 90px;
                padding: 15px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .filters-container {
                flex-direction: column;
                gap: 10px;
            }

            .filter-group {
                min-width: 100%;
            }

            .review {
                padding: 15px;
            }

            .review h3 {
                font-size: 1.1rem;
            }

            .review-text {
                font-size: 0.9rem;
            }

            .review-meta {
                font-size: 0.8rem;
            }

            .delete-review {
                top: 10px;
                right: 10px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin-top: 80px;
                width: 100%;
                border-radius: 0;
                border-left: none;
                border-right: none;
            }

            .review {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Мои отзывы</h1>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="search-box">
            <form method="GET">
                <div class="filters-container">
                    <div class="filter-group">
                        <label>Поиск по отзывам</label>
                        <input type="text" name="search" class="search-input" placeholder="Введите текст..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>

                    <div class="filter-group">
                        <label>Сортировка</label>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?= $sort_order == 'newest' ? 'selected' : '' ?>>Сначала новые</option>
                            <option value="oldest" <?= $sort_order == 'oldest' ? 'selected' : '' ?>>Сначала старые</option>
                            <option value="highest" <?= $sort_order == 'highest' ? 'selected' : '' ?>>Высокий рейтинг</option>
                            <option value="lowest" <?= $sort_order == 'lowest' ? 'selected' : '' ?>>Низкий рейтинг</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Рейтинг</label>
                        <select name="rating" onchange="this.form.submit()">
                            <option value="all" <?= $filter_rating == 'all' ? 'selected' : '' ?>>Все рейтинги</option>
                            <option value="5" <?= $filter_rating == '5' ? 'selected' : '' ?>>5 звёзд</option>
                            <option value="4" <?= $filter_rating == '4' ? 'selected' : '' ?>>4 звезды</option>
                            <option value="3" <?= $filter_rating == '3' ? 'selected' : '' ?>>3 звезды</option>
                            <option value="2" <?= $filter_rating == '2' ? 'selected' : '' ?>>2 звезды</option>
                            <option value="1" <?= $filter_rating == '1' ? 'selected' : '' ?>>1 звезда</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Статус</label>
                        <select name="status" onchange="this.form.submit()">
                            <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Все статусы</option>
                            <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>На модерации</option>
                            <option value="approved" <?= $filter_status == 'approved' ? 'selected' : '' ?>>Опубликован</option>
                            <option value="rejected" <?= $filter_status == 'rejected' ? 'selected' : '' ?>>Отклонён</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="search-btn">Применить фильтры</button>
            </form>
        </div>

        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <div class="no-reviews">
                    <i class="far fa-comment-dots" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Отзывов не найдено</h3>
                    <p><?= empty($search_query) ? 'У вас пока нет оставленных отзывов' : 'Попробуйте изменить параметры поиска' ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <?php if ($review['status'] == 'pending' || $review['status'] == 'rejected'): ?>
                            <form method="post" action="delete_review.php" style="display: inline;">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <button type="submit" class="delete-review" title="Удалить отзыв" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($review['product_name']) ?></h3>
                        <div class="rating" title="Рейтинг: <?= $review['rating'] ?>/5">
                            <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                        </div>
                        <div class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></div>
                        <div class="review-meta">
                            <span class="author"><i class="fas fa-user"></i> <?= htmlspecialchars($review['username']) ?></span>
                            <span class="date"><i class="far fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                            <span class="review-status" style="color: <?= getStatusColor($review['status']) ?>">
                                <i class="fas fa-info-circle"></i> <?= displayStatus($review['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>