<?php
session_start();
require_once 'config.php';
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
            padding: 0;
            line-height: 1.6;
            background-color: #121212;
            color: #F5F5F5;
        }

        .main-container {
            max-width: 1200px;
            margin: 110px auto 0;
            padding: 0 20px;
        }

        .hero-section {
            position: relative;
            height: 60vh;
            min-height: 500px;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('img/about-hero.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 80px;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
            animation: fadeIn 1.5s ease;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 300;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #FFFFFF;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            font-weight: 300;
            margin-bottom: 30px;
            opacity: 0.9;
            letter-spacing: 2px;
            color: #B0B0B0;
        }


        .section {
            margin-bottom: 80px;
            position: relative;
        }

        .section-title {
            font-size: 48px;
            font-weight: 300;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
            color: #F5F5F5;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: #8B5A2B;
        }

        .section-text {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 30px;
            max-width: 800px;
            color: #B0B0B0;
        }

        /* Timeline */
        .timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: #8B5A2B;
            transform: translateX(-50%);
        }

        .timeline-item {
            padding: 20px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-content {
            padding: 30px;
            background: #1E1E1E;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid #2E2E2E;
        }

        .timeline-date {
            font-weight: 500;
            color: #D4A76A;
            margin-bottom: 10px;
        }

        .timeline-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #D4A76A;
        }

        .timeline-item:nth-child(odd):before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            right: -10px;
            background-color: #8B5A2B;
            border-radius: 50%;
            top: 40px;
            z-index: 1;
        }

        .timeline-item:nth-child(even):before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            left: -10px;
            background-color: #8B5A2B;
            border-radius: 50%;
            top: 40px;
            z-index: 1;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .team-member {
            background: #1E1E1E;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
            border: 1px solid #2E2E2E;
        }

        .team-member:hover {
            transform: translateY(-10px);
        }

        .member-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .member-info {
            padding: 20px;
        }

        .member-name {
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: #D4A76A;
        }

        .member-position {
            color: #B0B0B0;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .value-card {
            background: #1E1E1E;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #2E2E2E;
            transition: transform 0.3s;
            text-align: center;
        }

        .value-card:hover {
            transform: translateY(-5px);
        }

        .value-icon {
            font-size: 2.5rem;
            color: #8B5A2B;
            margin-bottom: 20px;
        }

        .value-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #D4A76A;
        }

        /* Contact Section */
        .contact-section {
            background: #1E1E1E;
            padding: 50px;
            border-radius: 8px;
            margin-top: 80px;
            border: 1px solid #2E2E2E;
            max-width: 1000px;
            margin: 0 auto;
        }

        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
            justify-content: space-between;
            align-items: flex-start;
        }

        .contact-info {
            flex: 1;
            min-width: 300px;
        }

        .contact-title {
            font-size: 2rem;
            margin-bottom: 30px;
            color: #FFFFFF;
            position: relative;
        }

        .contact-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 2px;
            background: #8B5A2B;
        }

        .contact-details {
            margin-top: 30px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .contact-icon {
            font-size: 1.2rem;
            color: #8B5A2B;
            margin-right: 15px;
            margin-top: 3px;
        }

        .contact-text {
            flex: 1;
        }

        .contact-text p {
            margin: 0;
            line-height: 1.6;
            color: #B0B0B0;
        }

        .contact-text p strong {
            color: #F5F5F5;
        }

        .contact-map {
            flex: 1;
            min-width: 300px;
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #2E2E2E;
        }

        .contact-map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }


        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }


        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.8rem;
            }

            .hero-section {
                height: 50vh;
                min-height: 400px;
            }

            .section-title {
                font-size: 2.5rem;
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .contact-container {
                flex-direction: column;
            }

            .contact-map {
                margin-top: 30px;
                height: 350px;
            }

            .timeline-item {
                width: 100%;
                padding: 15px 30px;
            }

            .timeline-item:nth-child(even) {
                left: 0;
            }

            .timeline-item:nth-child(odd):before,
            .timeline-item:nth-child(even):before {
                left: -8px;
                right: auto;
            }

            .timeline:before {
                left: 8px;
            }
        }

        @media (max-width: 767px) {
            .hero-title {
                font-size: 2rem;
                letter-spacing: 1px;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-section {
                height: 40vh;
                min-height: 300px;
                margin-bottom: 40px;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .section-text {
                font-size: 1rem;
            }

            .team-grid,
            .values-grid {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                padding: 10px 20px 10px 40px;
            }

            .timeline-content {
                padding: 20px;
            }

            .member-image {
                height: 250px;
            }

            .contact-section {
                padding: 30px;
            }

            .contact-title {
                font-size: 1.5rem;
            }

            .value-card {
                padding: 20px;
            }

            .value-icon {
                font-size: 2rem;
            }

            .value-title {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 0 15px;
            }

            .hero-title {
                font-size: 1.8rem;
            }

            .hero-section {
                height: 35vh;
                min-height: 250px;
            }

            .section-title {
                font-size: 1.5rem;
                margin-bottom: 30px;
            }

            .section-title:after {
                width: 50px;
                bottom: -10px;
            }

            .contact-section {
                padding: 20px;
            }

            .contact-info,
            .contact-map {
                min-width: 100%;
            }

            .contact-map {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Добро пожаловать в LUV</h1>
            <p class="hero-subtitle">Как все начиналось</p>
        </div>
    </section>

    <div class="main-container">
        <section class="section">
            <h2 class="section-title">Кто мы</h2>
            <p class="section-text">
                LUV — это не просто магазин оптики. Это сообщество людей, которые верят, что хорошее зрение должно быть стильным и доступным.
                Наша миссия — изменить представление о том, какими должны быть очки.
            </p>
            <p class="section-text">
                Мы объединяем передовые технологии, мастерство лучших оптиков и современный дизайн, чтобы создать продукт,
                который вы полюбите с первого взгляда.
            </p>
        </section>

        <!-- Timeline Section -->
        <section class="section">
            <h2 class="section-title">Наш путь</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3 class="timeline-title">Начало</h3>
                        <p>Раньше Иван писал код, пока глаза не начинали сливаться с монитором. Теперь он помогает таким же, как он, — в его оптике LUV очки не просто корректируют зрение, а защищают его.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3 class="timeline-title">Для работы</h3>
                        <p>Здесь делают очки для программистов: с фильтром синего света, широкими линзами для комфортного чтения кода и лёгкими оправами, которые не давят даже после многочасовых митингов.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3 class="timeline-title">Для профилактики</h3>
                        <p>Иван настаивает: даже если зрение пока идеальное, защитные линзы — как ежедневный SPF для глаз. В салоне подберут нулевки с защитой от экранов, чтобы усталость и сухость не превратились в близорукость.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3 class="timeline-title">Для стиля</h3>
                        <p>Но если хочется просто красивый аксессуар — пожалуйста: имиджевые оправы от минимализма до смелых форм и солнцезащитные очки с UV-400 (потому что ультрафиолет вредит глазам даже зимой).</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h3 class="timeline-title">Диагностика без стресса</h3>
                        <p>— Никаких пугающих таблиц: точная проверка на современном оборудовании.
— Можно записаться онлайн — без очередей и лишних вопросов.</p>
                    </div>
                </div>
            </div>
        </section>



        <!-- Values Section -->
        <section class="section">
            <h2 class="section-title">Наши ценности</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-lightbulb"></i></div>
                    <h3 class="value-title">Инновации</h3>
                    <p>Ежегодно мы инвестируем 15% прибыли в разработку новых технологий. Умные покрытия линз, эргономичные оправы будущего — мы создаём то, что сделает ваше зрение ещё лучше.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-hands-helping"></i></div>
                    <h3 class="value-title">Поддержка</h3>
                    <p>Пожизненная гарантия на все оправы и бесплатное обслуживание (регулировка, замена носоупоров). Ваши очки должны служить вам годами — мы поможем в этом.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-smile"></i></div>
                    <h3 class="value-title">Радость</h3>
                    <p>Очки — это не просто необходимость. Это стиль, комфорт и удовольствие каждый день. Мы подберём то, что будет радовать вас каждый раз, когда вы их надеваете.</p>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section">
            <div class="contact-container">
                <div class="contact-info">
                    <h2 class="contact-title">Как нас найти</h2>
                    <p>Мы всегда рады видеть вас в нашем салоне или ответить на любые вопросы по телефону и email.</p>

                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-text">
                                <p><strong>г. Москва, ул. Маросейка 2/15, стр. 1, (этаж 2)</strong></p>
                                <p>Ежедневно с 10:00 до 21:00</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-text">
                                <p><strong>+7 (919) 970-95-07</strong></p>
                                <p>Консультация: Пн-Пт с 11:00 до 19:00</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-text">
                                <p><strong>LuvInfo@mail.ru</strong></p>
                                <p>Пишите по всем вопросам</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2245.3737899857366!2d37.61842331593095!3d55.75170498055286!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46b54a5a738fa419%3A0x7c347d506f52311f!2z0JrRgNCw0YHQvdCw0Y8g0YPQuy4sIDE1LCDQnNC-0YHQutCy0LAsINCg0L7RgdGB0LjRjywgMTA3MDc4!5e0!3m2!1sru!2sru!4v1620000000000!5m2!1sru!2sru" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </section>
</div>
    <?php include 'footer.php'; ?>

    <script>
        // Анимация при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.timeline-item, .team-member, .value-card');

                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;

                    if (elementPosition < windowHeight - 100) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };

            // Инициализация анимации
            window.addEventListener('load', function() {
                const elements = document.querySelectorAll('.timeline-item, .team-member, .value-card');

                elements.forEach(element => {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(30px)';
                    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                });

                setTimeout(animateOnScroll, 500);
            });

            window.addEventListener('scroll', animateOnScroll);
        });
    </script>
</body>
</html>