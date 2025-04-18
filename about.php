<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О нас - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
    <style>
		body {
            background-color: #f8f8f8;
            position: relative;
        }
        .about-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .about-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 800px;
            width: 100%;
        }
        .slogan {
            font-size: 28px;
            font-weight: bold;
            color: #FF69B4;
            margin-top: 20px;
        }
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: 20px auto;
        }
        .slide {
            display: none;
            width: 100%;
            border-radius: 10px;
        }
        .active {
            display: block;
        }
		.logo-bg {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 350px;
            opacity: 0.2;
        }
        .logo-left {
            left: 5%;
        }
        .logo-right {
            right: 5%;
        }
    </style>
</head>
<body>
<img src="images/logo.png" class="logo-bg logo-left" alt="Логотип">
<img src="images/logo.png" class="logo-bg logo-right" alt="Логотип">
<main>
    <div class="about-wrapper">
        <div class="about-container">
            <h2>О нас</h2>
            <p>ClothesForU – это магазин для тех, кто ценит стиль и моду.</p>
            <p>Мы предлагаем широкий выбор модных вещей на любой вкус.</p>
            <p>Следим за последними трендами и обновляем коллекции каждую неделю.</p>
            <p>Качество и комфорт – вот что делает нашу одежду особенной.</p>
            <p>Открой для себя мир стиля вместе с ClothesForU!</p>

            <div class="slogan">ClothesForU – твой стиль, твой выбор!</div>

            <div class="slideshow-container">
                <img class="slide active" src="images/slide1.jpg" alt="Модная одежда">
                <img class="slide" src="images/slide2.jpg" alt="Сочетания">
                <img class="slide" src="images/slide3.jpg" alt="Тренды сезона">
            </div>
        </div>
    </div>
</main>

<script>
    let slideIndex = 0;
    const slides = document.querySelectorAll(".slide");

    function showSlides() {
        slides.forEach((slide, index) => {
            slide.style.display = "none";
        });
        slideIndex++;
        if (slideIndex > slides.length) { slideIndex = 1; }
        slides[slideIndex - 1].style.display = "block";
        setTimeout(showSlides, 3000); // Меняет слайд каждые 3 секунды
    }

    showSlides();
</script>

</body>
</html>

<?php include('footer.php'); ?>
