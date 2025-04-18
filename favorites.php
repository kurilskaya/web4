<?php
include('db_connection.php');
include('header.php');
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Перенаправление на страницу авторизации
    exit();
}

$user_id = $_SESSION['user_id']; // ID текущего пользователя

// SQL-запрос для получения товаров, добавленных в избранное
$query_favorites = "
    SELECT p.product_id, p.product_name, p.price, p.photo 
    FROM favorites f
    JOIN assortment p ON f.product_id = p.product_id
    WHERE f.user_id = :user_id";

$stmt_favorites = $pdo->prepare($query_favorites);
$stmt_favorites->execute([':user_id' => $user_id]);
$favorites = $stmt_favorites->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
    <style>
               
        /* Контейнер с градиентом, когда нет избранного */
        .empty-favorites {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            font-size: 28px;
            color: #fff;
            width: auto; 
            max-width: 600px; 
            padding: 40px 30px; 
            border-radius: 20px;
            background: linear-gradient(135deg, #f5c8e2, #f7a9cd); /* Нежный розовый градиент */
            box-shadow: 0 10px 30px rgba(255, 105, 180, 0.5); /* Легкая тень с розовым оттенком */
            font-weight: bold;
            text-shadow: 1px 1px 4px rgba(255, 105, 180, 0.5); /* Мягкая тень текста */
            animation: fadeIn 1.2s ease-in-out;
            position: relative;
            z-index: 1;
            margin: 50px auto; /* Центрируем на странице */
        }

        /* Стиль для карточек товаров */
        .favorites-page {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 40px;
        }
        .favorite-card {
            width: 250px;
            background-color: white;
            margin: 15px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
            position: relative; /* Добавляем для абсолютного позиционирования иконки */
        }

        .favorite-card:hover {
            transform: scale(1.05);
        }

        .favorite-card img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 10px;
        }

        .favorite-card h3 {
            font-size: 20px;
            margin: 10px 0;
        }

        .favorite-card p {
            font-size: 18px;
            color: #888;
            margin-bottom: 15px;
        }

        .favorite-card .favorite-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .favorite-card .favorite-icon img {
            width: 24px;
            height: 24px;
            transition: transform 0.2s ease-in-out;
        }

        .favorite-card .favorite-icon:hover img {
            transform: scale(1.1);
        }
		
		

    </style>
</head>
<body>

<main>
    <div class="favorites-page">
        <?php if (empty($favorites)): ?>
            <div class="empty-favorites">У вас пока нет избранного</div>
        <?php else: ?>
            <?php foreach ($favorites as $favorite): ?>
                <div class="favorite-card" data-product-id="<?= $favorite['product_id'] ?>">
                    <img src="<?= $favorite['photo'] ?>" alt="<?= htmlspecialchars($favorite['product_name']) ?>">
                    <div class="favorite-icon" onclick="toggleFavorite(<?= $favorite['product_id'] ?>, this, event)">
                        <img class="heart-icon" src="heart_liked.svg" alt="В избранном">
                    </div>
                    <h3><?= htmlspecialchars($favorite['product_name']) ?></h3>
                    <p><?= number_format($favorite['price'], 2, ',', ' ') ?> ₽</p>
                    <a href="product.php?id=<?= $favorite['product_id'] ?>" class="btn">Посмотреть товар</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleFavorite(productId, iconContainer, event) {
    // Останавливаем всплытие события, чтобы клик по сердечку не вызывал переход на карточку товара
    event.stopPropagation();

    const card = iconContainer.closest('.favorite-card');
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'toggle_favorite.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Переключаем изображение сердечка
            const icon = iconContainer.querySelector('.heart-icon');
            if (icon.src.includes('heart_liked.svg')) {
                icon.src = 'heart_unliked.svg';
            } else {
                icon.src = 'heart_liked.svg';
            }

            // Удаляем карточку из избранного
            card.remove();
        }
    };
    xhr.send('product_id=' + productId);
}
</script>

</body>
</html>
