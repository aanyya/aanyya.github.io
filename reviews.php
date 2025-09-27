<?php
session_start();
require_once 'config.php';

// Параметры фильтрации и сортировки
$search_query = $_GET['search'] ?? '';
$sort_order = $_GET['sort'] ?? 'newest';
$filter_rating = $_GET['rating'] ?? 'all';

try {
    // Базовый запрос для отзывов
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
              WHERE pr.status = 'approved'";

    // Добавляем условия поиска
    $params = [];
    if (!empty($search_query)) {
        $query .= " AND (u.username LIKE ? OR
                        pr.review_text LIKE ? OR
                        il.name LIKE ? OR
                        isun.name LIKE ? OR
                        iv.name LIKE ? OR
                        iimg.name LIKE ? OR
                        icomp.name LIKE ?)";
        $search_param = "%$search_query%";
        $params = array_fill(0, 7, $search_param);
    }

    // Фильтр по рейтингу
    if ($filter_rating != 'all' && is_numeric($filter_rating)) {
        $query .= " AND pr.rating = ?";
        $params[] = $filter_rating;
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
            background-color: #121212;
            color: #F5F5F5;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background: #1E1E1E;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-top: 110px;
            border: 1px solid #2E2E2E;
        }

        .container-ob{
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        h1, h2, h3 {
            color: #F5F5F5;
            font-weight: 500;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #2E2E2E;
            padding-bottom: 1rem;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #D4A76A;
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #8B5A2B;
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
            background: #2A2A2A;
            border-radius: 8px;
            border: 1px solid #2E2E2E;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .review:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .review h3 {
            color: #D4A76A;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .rating {
            color: #FFD700;
            font-size: 1.2rem;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .review-text {
            margin-bottom: 15px;
            line-height: 1.7;
        }

        .review-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #B0B0B0;
        }

        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #B0B0B0;
            border: 1px dashed #2E2E2E;
            border-radius: 8px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            color: #B0B0B0;
            font-size: 14px;
        }

        select, .search-input {
            padding: 8px 15px;
            background-color: #1E1E1E;
            border: 1px solid #2E2E2E;
            color: #F5F5F5;
            border-radius: 6px;
            cursor: pointer;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-input {
            width: 300px;
            max-width: 100%;
        }

        .search-btn {
            padding: 8px 15px;
            background-color: transparent;
            color: #D4A76A;
            border: 1px solid #D4A76A;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }

        .search-btn:hover {
            background-color: #D4A76A;
            color: #121212;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin-top: 90px;
            }

            .search-box {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .search-input {
                width: 100%;
            }

            .search-btn {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>Отзывы на товары</h1>

        <div class="container-ob">
                   <div class="search-box">
            <form method="GET">
                <input type="text" name="search" class="search-input" placeholder="Поиск по отзывам..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="search-btn">Поиск</button>
            </form>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label>Сортировка</label>
                <select name="sort" onchange="updateFilters()">
                    <option value="newest" <?= $sort_order == 'newest' ? 'selected' : '' ?>>Сначала новые</option>
                    <option value="oldest" <?= $sort_order == 'oldest' ? 'selected' : '' ?>>Сначала старые</option>
                    <option value="highest" <?= $sort_order == 'highest' ? 'selected' : '' ?>>Высокий рейтинг</option>
                    <option value="lowest" <?= $sort_order == 'lowest' ? 'selected' : '' ?>>Низкий рейтинг</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Фильтр по рейтингу</label>
                <select name="rating" onchange="updateFilters()">
                    <option value="all" <?= $filter_rating == 'all' ? 'selected' : '' ?>>Все рейтинги</option>
                    <option value="5" <?= $filter_rating == '5' ? 'selected' : '' ?>>5 звёзд</option>
                    <option value="4" <?= $filter_rating == '4' ? 'selected' : '' ?>>4 звезды</option>
                    <option value="3" <?= $filter_rating == '3' ? 'selected' : '' ?>>3 звезды</option>
                    <option value="2" <?= $filter_rating == '2' ? 'selected' : '' ?>>2 звезды</option>
                    <option value="1" <?= $filter_rating == '1' ? 'selected' : '' ?>>1 звезда</option>
                </select>
            </div>
        </div>
        </div>

        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <div class="no-reviews">
                    <i class="far fa-comment-dots" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Отзывов не найдено</h3>
                    <p>Попробуйте изменить параметры поиска</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <h3><?= htmlspecialchars($review['product_name']) ?></h3>
                        <div class="rating" title="Рейтинг: <?= $review['rating'] ?>/5">
                            <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                        </div>
                        <div class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></div>
                        <div class="review-meta">
                            <span class="author"><i class="fas fa-user"></i> <?= htmlspecialchars($review['username']) ?></span>
                            <span class="date"><i class="far fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function updateFilters() {
            const sort = document.querySelector('select[name="sort"]').value;
            const rating = document.querySelector('select[name="rating"]').value;
            const search = '<?= htmlspecialchars($search_query) ?>';

            let url = `reviews.php?sort=${sort}&rating=${rating}`;
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }

            window.location.href = url;
        }
    </script>
</body>
</html>