<header class="header">
    <nav class="header-left">
        <div class="burger-menu" id="burgerMenu">
            <a class="menu" href="#"><img src="img/extension.svg" alt=""></a>
        </div>
        <a class="header-number" href="tel:+79199709507">+7 (919) 970-95-07</a>
    </nav>
    <div>
        <a href="/"><img class="logo-w" src="img/luw-logo.svg" alt=""></a>
    </div>
    <nav class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/kabinet.php"><img src="img/vhod.svg" alt="Личный кабинет"></a>
        <?php else: ?>
            <a href="/vhod.php"><img src="img/vhod.svg" alt="Вход"></a>
        <?php endif; ?>

<a href="/favorites.php" class="favorites-link">
    <img src="img/favorites.svg" alt="Избранное">
    <?php if (isset($_SESSION['favorites_count']) && $_SESSION['favorites_count'] > 0): ?>
    <?php endif; ?>
</a>

        <a href="/cart.php" class="cart-link">
            <img src="img/bag.svg" alt="Корзина">
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
            <?php endif; ?>
        </a>
        <a href="collection_glasses.php"><img src="img/search.svg" alt="Поиск"></a>
    </nav>

    <!-- Боковое меню -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Категории</h3>
            <button class="close-btn" id="closeSidebar">&times;</button>
        </div>

        <ul class="sidebar-categories">
            <?php
            $categories = [
                "/collection_glasses.php" => "Коллекция очков",
                "/vision.php" => "Очки для зрения",
                "/image.php" => "Имиджевые очки",
                "/computer.php" => "Компьютерные очки",
                "/sun.php" => "Солнцезащитные очки",
                "/lenses.php" => "Линзы",
                "/about_us.php" => "О нас",
                "/reviews.php" => "Отзывы",
            ];

            foreach ($categories as $link => $title) {
                echo '<li><a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($title) . '</a></li>';
            }
            ?>
        </ul>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        // JavaScript для работы меню
        document.addEventListener('DOMContentLoaded', function() {
            const burgerMenu = document.getElementById('burgerMenu');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const closeSidebar = document.getElementById('closeSidebar');

            // Открытие меню
            burgerMenu.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.add('open');
                sidebarOverlay.classList.add('open');
                burgerMenu.classList.add('open');
            });

            // Закрытие меню
            function closeMenu() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
                burgerMenu.classList.remove('open');
            }

            closeSidebar.addEventListener('click', closeMenu);
            sidebarOverlay.addEventListener('click', closeMenu);

            // Закрытие при клике на ссылку
            document.querySelectorAll('.sidebar-categories a').forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(closeMenu, 300);
                });
            });
        });
    </script>
</header>