<?php
// Подключаем файл для подключения к базе данных
include('db_connection.php');
?>

<header>
    <!-- Логотип -->
	
	<div class="logo">
		<a href="index.php">ClothesForU</a>
		<img src="images/logo.png" alt="ClothesForU Logo" class="logo-img">
	</div>
	<style>
		.logo {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.logo-img {
			height: 40px;
		}
	</style>




    <!-- Навигационное меню -->
    <nav class="menu">
        <div class="menu-item"><a href="index.php">Главная</a></div>
        <div class="menu-item"><a href="catalog.php">Каталог</a></div>
        <div class="menu-item"><a href="about.php">О нас</a></div>
		<div class="menu-item"><a href="news.php">Новости</a></div>
    </nav>

    <!-- Иконки для аккаунта/корзины -->
    <div class="user-actions">
        <a href="login.php" class="user-icon">Аккаунт</a>
		<a href="favorites.php" class="user-icon">Избранное</a>
        <a href="cart.php" class="user-icon">Корзина</a>
    </div>
</header>

<!-- Подключаем стили для хедера -->
<link rel="stylesheet" href="header.css">