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
        /* font-family: Arial, sans-serif;*/
            margin: 0;
            padding: 0;
            background-color: #0e0e0e;
        }

        .universe-luv{
            text-align: center;
            padding-top: 100px;
            color: white;
            max-width: 1030px;
            margin: 0 auto;
        }
        .universe-luv_text{
            font-size: 47px;
            font-weight: 800;
        }
        .universe-luv_block{
            display: flex;

        }
        .text-container {
              position: relative;
              padding-top: 40px;
              font-weight: 400;
        }
        .text-block {
              position: absolute;
              opacity: 0;
              transition: opacity 1s ease-in-out;
              color: white;
              font-size: 19px;
              /* Дополнительные стили для текста */
        }
        .text-block.active {
              opacity: 1;
        }


        .carousel-container {
  perspective: 1000px; /* Глубина 3D-пространства */
  width: 300px;
  height: 300px;
  margin: 0 auto;
  padding: 200px 0px 200px 0px;
}

.carousel {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  animation: rotate 30s infinite linear;
  transform: rotateX(15deg);  /* Наклон карусели */
}

.carousel img {
  position: absolute;
  width: 200px;
  height: 150px;
  object-fit: cover;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.3);
  transition: all 0.5s ease;
}

/* Расположение изображений по кругу */
.carousel img:nth-child(1) { transform: rotateY(0deg) translateZ(250px); }
.carousel img:nth-child(2) { transform: rotateY(60deg) translateZ(250px); }
.carousel img:nth-child(3) { transform: rotateY(120deg) translateZ(250px); }
.carousel img:nth-child(4) { transform: rotateY(180deg) translateZ(250px); }
.carousel img:nth-child(5) { transform: rotateY(240deg) translateZ(250px); }
.carousel img:nth-child(6) { transform: rotateY(300deg) translateZ(250px); }
.carousel img:nth-child(7) { transform: rotateY(300deg) translateZ(250px); }

@keyframes rotate {
  from { transform: rotateX(15deg) rotateY(0deg); }
  to { transform: rotateX(15deg) rotateY(360deg); }
}

.bnt-more{
    font-weight: 600;
    border: 1.5px solid;
}

/* Дополнительный эффект при наведении */
/*.carousel:hover {
  animation-play-state: paused;
}*/
/*
.carousel:hover img {
  transform: scale(1.1) translateZ(250px);  Увеличиваем и возвращаем Z
  filter: brightness(1.2);
  transition: all 0.3s ease;
}*/
.luv-care_ob{
    background-color: #F8F8F8;
}
.luv-care_heading{
    text-align: center;
    max-width: 650px;
    margin: 0 auto;
}



		.slider-container {
            width: 100%;
            overflow: hidden;
            position: relative;
            margin: 0 auto;
        }

        .slider {
            display: flex;
            transition: transform 0.5s ease;
        }

        .slide {
            min-width: 100%;
            box-sizing: border-box;
        }

        .slide img {
            width: 100%;
            height: auto;
            display: block;
        }

        .slider-buttons {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        .slider-button {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        .slider-button:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }



        .luv-care_textB{
            font-size: 35px;
            padding-top: 50px;
        }
        .luv-care_textM{
            font-size: 17px;
            padding: 20px 0px;
        }



/* Стили для блока отзывов */
.reviews-section {
    background-color: #f8f8f8;
    padding: 80px 0;
}

.reviews-slider-container {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    overflow: hidden;
}

.reviews-slider {
    display: flex;
    transition: transform 0.5s ease;
    will-change: transform;
}

.review-slide {
    min-width: 100%;
    box-sizing: border-box;
    padding: 0 20px;
    flex-shrink: 0;
}

.review-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.rating {
    color: gold;
    font-size: 24px;
    margin-bottom: 15px;
}

.review-text {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 20px;
    font-style: italic;
    min-height: 80px;
}

.review-author {
    font-weight: bold;
    margin-bottom: 5px;
}

.review-product {
    color: #666;
    font-size: 14px;
}

.reviews-nav {
    text-align: center;
    margin-top: 20px;
}

.reviews-nav-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #ccc;
    margin: 0 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.reviews-nav-dot.active {
    background-color: #333;
}







                .container {
            width: 80%;
            margin: 30px auto;
        }

               .menu_2 {
            display: flex;
            flex-direction: row;
            justify-content: space-around;
                align-items: center;
        }

        .but_menu {
            border: 1px solid #CFE3EA; ;
            font-size: 12px;
            border-radius: 30px;
            background-color: #CFE3EA;
            color: black;
            width: 160px;
        }

        .but_menu:hover {
            background-color: #CFE3EA;
        }
      .menu_ph{
      	width: 120px;
      }

      .menu_21{
      	display: flex;
      	flex-direction: column;
      	display: block;
      }
              .reg {
            display: flex;
            flex-direction: column;
        }

        .reg_but {
            display: flex;
            flex-direction: row;
            justify-content: space-around;
        }

        .reg_reg {
            border: 1px solid #CFE3EA  ;
            font-size: 20px;
            border-radius: 30px;
            background-color: #CFE3EA ;
            color: black;
            width: 380px;
            height: 86px;
        }

        .reg_reg:hover {
            background-color: #CFE3EA;
        }

        .reg_word {
            display: flex;
            justify-content: center;
            text-align: center;
        }
        .cards-container {
            display: flex;
            gap: 20px;
            max-width: 1090px;
            padding: 20px;
            margin: 0 auto;
        }

        .card {
            flex: 1;
            height: 350px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .black-card {
            background-color: #000;
            color: white;
            padding-top: 30px;
        }

        .white-card {
            background-color: #fff;
            color: #333;
        }

        .card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }

        .white-card .card-image {
            opacity: 1;
        }

        .card-content {
            position: absolute;
            top: -3px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-sizing: border-box;
        }

        .card-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 16px;
        }



        /* Contact Section */
        .contact-section {
            background: #0e0e0e;
            padding: 50px;
            border-radius: 8px;
            margin:50px 0 50px 0;
            border: 1px solid #0e0e0e;
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
            color:  white;
            font-size: 17px;
        }

        .contact-title {
            font-size: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            color:  white;
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


@media (max-width: 992px) {
    .universe-luv_text {
        font-size: 36px;
    }

    .carousel-container {
        padding: 100px 0;
    }

    .luv-care_textB {
        font-size: 28px;
    }

    .cards-container {
        flex-direction: column;
    }

    .card {
        height: 250px;
    }

    .oprava-love-container {
        flex-direction: column;
    }

    .opravalove-image {
        width: 100%;
    }

    .oprava-love-content {
        padding: 20px;
    }
}


@media (max-width: 576px) {
    .universe-luv_text {
        font-size: 24px;
    }

    .text-block {
        font-size: 16px;
    }

    .carousel-container {
        padding: 50px 0;
    }

    .luv-care_textB {
        font-size: 22px;
    }

    .luv-care_textM {
        font-size: 14px;
    }

    .oprava-love-contentB p:first-child {
        font-size: 24px;
        line-height: 1.3;
    }

    .oprava-love-contentM {
        grid-template-columns: 1fr;
    }

    .reviews-section h2 {
        font-size: 24px;
    }

    .review-content {
        padding: 15px;
    }

    .slider-title {
        font-size: 24px;
    }
}




        @media (max-width: 768px) {
            .h1_main {
                font-size: 48px;
            }
            .but_1 {
                font-size: 24px;
                        margin: 0 0 30px 0;
            }
            .menu_2 {
                flex-direction: column;
                align-items: center;
                gap: 30px;
            }
            .menu_21 {
                width: 90%;
                display: contents;
            }
            .reg_reg {
                width: 100%;
            }
            .first_part{
                display: flex;
                flex-direction: column;
                text-align: center;
            }
            .about_us {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                padding: 20px;
            }

            .about_us_inner {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 30px;
                max-width: 1200px;
                width: 100%;
            }

            .ab_1 {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .ab_2 {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .ab_23 {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .menu_ph{
                width: 50%;
                display: flex;
                justify-content: center;
            }

            .ab_25{
                width: auto;
                max-width: 100%;
                justify-content: center;
            }
            .reg_but {
                display: flex;
                flex-direction: column;
                text-align: center;
                align-items: center;
                justify-content: center;
                width: 100%;
                gap: 30px;
            }
                  .menu_ph{
      	width: 30%;
      }
        }




        @media (max-width: 320px) {
            .f_1{
                width: 100%;
            }
            .but_1{
                width: 90%;
            }
            .ab_24{
                width: 90%;
            }
                  .menu_ph{
      	width: 30%;
      }
	</style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="main-hero-slider">
    <div class="main-hero-slides">
        <div class="main-slide active"><img src="img/slider1.jpeg" alt="Slider Image 1" loading="lazy"></div>
        <div class="main-slide"><img src="img/slider2.jpeg" alt="Slider Image 2" loading="lazy"></div>
        <div class="main-slide"><img src="img/slider3.jpeg" alt="Slider Image 3" loading="lazy"></div>
    </div>

    <!-- Текст поверх слайдера -->
    <div class="slider-text-overlay">
        <h1 class="slider-title">LUV — забота о зрении</h1>
    </div>

    <div class="slider-controls">
        <button class="slider-prev">←</button>
        <button class="slider-next">→</button>
    </div>
</section>

<style>
    /* Main Slider Styles */
    .main-hero-slider {
        position: relative;
        height: 90vh;
        max-height: 1000px;
        overflow: hidden;
        background-color: #000;
    }

    .main-hero-slides {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .main-slide {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }

    .main-slide.active {
        opacity: 1;
    }

    .main-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        filter: brightness(70%);
    }

    /* Стили для текста поверх слайдера */
    .slider-text-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 5;
        width: 100%;
        text-align: center;
        padding: 0 20px;
        box-sizing: border-box;
    }

    .slider-title {
        color: white;
        font-size: 3.5vw;
        font-weight: 700;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        margin: 0;
        opacity: 0;
        transform: translateY(30px);
        animation: textAppear 2s ease-out forwards;
        animation-delay: 0.5s;
    }

    @keyframes textAppear {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .slider-controls {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .slider-prev, .slider-next {
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 16px;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .slider-prev:hover, .slider-next:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }

    @media (max-width: 768px) {
        .main-hero-slider {
            height: 50vh;
        }

        .slider-title {
            font-size: 6vw;
        }

        .slider-prev, .slider-next {
            width: 30px;
            height: 30px;
            font-size: 14px;
            padding: 8px;
        }
    }

    @media (max-width: 480px) {
        .slider-title {
            font-size: 8vw;
        }
    }
</style>


<div class="wrapper-">
<div class="universe-luv">
    <div class="">
        <p class="universe-luv_text">
            Вселенная LUV
        </p>
    </div>
    <div class="text-container ">
  <div class="text-block active">Мы — больше, чем оптика. LUV — это пространство, в котором гармонично сочетаются здоровье, стиль и комфорт. Сообщество, в котором люди разделяют близкие ценности и находят поддержку.</div>
  <div class="text-block">Это пространство, где забота о здоровье идет рука об руку со стилем. В одном месте можно проверить зрение, проконсультироваться, выбрать стильную оправу и отремонтировать любимые очки.</div>
  <div class="text-block">Это пространство для детей и взрослых, которые думают о своем будущем. Мы заботимся о том, чтобы они увидели мир четким и ярким через стильные красивые очки.</div>
</div>
</div>

<div class="carousel-container">
  <div class="carousel">
    <img src="img/carousel_1.png" alt="Image 1">
    <img src="img/carousel_2.png" alt="Image 1">
    <img src="img/carousel_3.png" alt="Image 1">
    <img src="img/carousel_4.png" alt="Image 1">
    <img src="img/carousel_5.png" alt="Image 1">
    <img src="img/carousel_6.png" alt="Image 1">
    <img src="img/carousel_7.png" alt="Image 1">
  </div>
</div>


    <div class="container-bnt__more">
        <a class="bnt-more" href="collection_glasses.php">посмотреть коллекции</a>
    </div>
</div>

</div>

<div class="luv-care_ob">
    <div class="luv-care_heading">
        <p class="luv-care_textB">LUV - клуб заботы о зрении и очках</p>
        <p class="luv-care_textM">Мы провели диагностику более 100000 глаз и позаботились о том, чтобы они стали видеть мир четким и ярким</p>
    </div>

 <div class="cards-container">
        <div class="card black-card">
            <img src="img/luv-care1.png" alt="Изображение 1" class="card-image">
            <div class="card-content">
                <p class="card-text">Уютное и комфортное пространство</p>
            </div>
        </div>

        <div class="card white-card">
            <img src="img/luv-care2.png" alt="Изображение 2" class="card-image">
            <div class="card-content">
                <p class="card-text">Новейшие технологии</p>
            </div>
        </div>
                <div class="card black-card">
            <img src="img/luv-care3.png" alt="Изображение 1" class="card-image">
            <div class="card-content">
                <p class="card-text">Внимательные специалисты</p>
            </div>
        </div>
    </div>

        <div class="container-bnt__more">
        <a class="bnt-more" href="about_us.php" style="color: black;">подробнее о клубе</a>
    </div>
</div>



<div class="oprava-love-container">
    <div class="opravalove-image">
        <img src="img/opravalove.jpg" alt="oprava-love" class="opravaloveimage">
    </div>
    <div class="oprava-love-content">
        <div class="oprava-love-contentB">
            <p style="font-size: 40px; font-weight: bold; line-height: 53px;">Оправы, в которых вы себе <span style="color: #A95F1F;">нравитесь</span></p>
            <p >У нас вы найдёте оправы на любой стиль, возраст и вкус — от модных подростков до серьезных профессионалов. Но главное — мы поможем найти ту, что подчёркивает лицо, не жмёт и делает «вау».</p>
            <p>Консультант не отпустит вас с тем, что «не сидит» — потому что нам правда не все равно.</p>
        </div>
        <div class="oprava-love-contentM">
            <div>
                <p class="oprava-love-contentMH">
                    Коллекция
                </p>
                <p class="">
                    оправ LUV + других топовых брендов
                </p>
            </div>
            <div>
                <p class="oprava-love-contentMH">
                    94% Лувтян
                </p>
                <p>
                    нашли «ту самую» оправу с 1 раза
                </p>
            </div>
            <div>
                <p class="oprava-love-contentMH">
                    6 000 +
                </p>
                <p>
                    подобранных для вас оправ
                </p>
            </div>
        </div>
        <a class="oprava-love-btn" href="collection_glasses.php">Подобрать оправу</a>
    </div>
</div>








<!-- Блок с отзывами -->
<div class="reviews-section">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 40px; font-size: 32px;">Отзывы наших клиентов</h2>

        <div class="reviews-slider-container">
            <div class="reviews-slider">
                <?php
                try {
                    // Получаем только 5-звездочные отзывы
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
                              WHERE pr.status = 'approved' AND pr.rating = 5
                              ORDER BY pr.created_at DESC
                              LIMIT 10";

                    $stmt = $pdo->query($query);
                    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($reviews)) {
                        foreach ($reviews as $review) {
                            echo '<div class="review-slide">';
                            echo '<div class="review-content">';
                            echo '<div class="rating">★★★★★</div>';
                            echo '<p class="review-text">' . nl2br(htmlspecialchars($review['review_text'])) . '</p>';
                            echo '<div class="review-author">' . htmlspecialchars($review['username']) . '</div>';
                            if (!empty($review['product_name'])) {
                                echo '<div class="review-product">Товар: ' . htmlspecialchars($review['product_name']) . '</div>';
                            }
                            echo '</div></div>';
                        }
                    } else {
                        // Заглушка, если нет отзывов
                        echo '<div class="review-slide">';
                        echo '<div class="review-content">';
                        echo '<div class="rating">★★★★★</div>';
                        echo '<p class="review-text">Наши клиенты очень довольны! Будьте первым, кто оставит отзыв.</p>';
                        echo '</div></div>';
                    }
                } catch (PDOException $e) {
                    // В случае ошибки просто не выводим блок с отзывами
                    error_log("Ошибка при загрузке отзывов: " . $e->getMessage());
                }
                ?>
            </div>

            <div class="reviews-nav" id="reviewsNav"></div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="reviews.php" class="bnt-more" style="color: black;">Все отзывы</a>
        </div>
    </div>
</div>

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
</div>



    <script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.main-slide');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    let currentIndex = 0;
    let slideInterval;

    function showSlide(index) {
        // Скрываем все слайды
        slides.forEach(slide => slide.classList.remove('active'));

        // Показываем текущий слайд
        slides[index].classList.add('active');
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(currentIndex);
    }

    function startAutoplay() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }

    // Инициализация слайдера
    showSlide(currentIndex);
    startAutoplay();

    // Обработчики событий для кнопок
    nextBtn.addEventListener('click', function() {
        clearInterval(slideInterval);
        nextSlide();
        startAutoplay();
    });

    prevBtn.addEventListener('click', function() {
        clearInterval(slideInterval);
        prevSlide();
        startAutoplay();
    });

    // Пауза при наведении
    const slider = document.querySelector('.main-hero-slider');
    slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
    slider.addEventListener('mouseleave', startAutoplay);
});


// карусель
       document.querySelector('.carousel').addEventListener('mouseenter', function() {
  this.style.animationPlayState = 'paused';
});

document.querySelector('.carousel').addEventListener('mouseleave', function() {
  this.style.animationPlayState = 'running';
});


        const slider = document.querySelector('.slider');
        const slides = document.querySelectorAll('.slide');
        const prev = document.getElementById('prev');
        const next = document.getElementById('next');
        let index = 0;

        function showSlide(idx) {
            slider.style.transform = `translateX(-${idx * 100}%)`;
        }

        prev.addEventListener('click', () => {
            index = (index - 1 + slides.length) % slides.length;
            showSlide(index);
        });

        next.addEventListener('click', () => {
            index = (index + 1) % slides.length;
            showSlide(index);
        });


        // Слайдер отзывов
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.reviews-slider');
    const slides = document.querySelectorAll('.review-slide');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    const navContainer = document.getElementById('reviewsNav');
    let currentIndex = 0;
    let autoSlideInterval;

    // Создаем точки навигации
    function createNavDots() {
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('reviews-nav-dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => goToSlide(index));
            navContainer.appendChild(dot);
        });
    }

    // Переход к конкретному слайду
    function goToSlide(index) {
        currentIndex = index;
        updateSlider();
    }

    // Обновление позиции слайдера и активной точки
    function updateSlider() {
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;

        // Обновляем активную точку
        document.querySelectorAll('.reviews-nav-dot').forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }

    // Следующий слайд
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlider();
    }

    // Предыдущий слайд
    function prevSlide() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateSlider();
    }

    // Автопрокрутка
    function startAutoSlide() {
        clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(nextSlide, 5000);
    }

    // Инициализация
    if (slides.length > 0) {
        createNavDots();
        startAutoSlide();

        // Обработчики событий для кнопок
        nextBtn.addEventListener('click', () => {
            clearInterval(autoSlideInterval);
            nextSlide();
            startAutoSlide();
        });

        prevBtn.addEventListener('click', () => {
            clearInterval(autoSlideInterval);
            prevSlide();
            startAutoSlide();
        });

        // Пауза при наведении
        slider.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
        slider.addEventListener('mouseleave', startAutoSlide);
    }
});



    </script>

</body>
</html>