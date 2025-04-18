<?php
include('db_connection.php');
include('header.php');
session_start();

// Получение ID товара из URL
$product_id = $_GET['id'] ?? 0;

// SQL-запрос для получения данных о товаре
$query_product = "SELECT * FROM assortment WHERE product_id = :product_id";
$stmt_product = $pdo->prepare($query_product);
$stmt_product->execute([':product_id' => $product_id]);
$product = $stmt_product->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Товар не найден');
}

// SQL-запрос для получения доступных размеров
$query_sizes = "
    SELECT s.size_name FROM product_sizes ps
    JOIN sizes s ON ps.size_id = s.size_id
    WHERE ps.product_id = :product_id";

$stmt_sizes = $pdo->prepare($query_sizes);
$stmt_sizes->execute([':product_id' => $product_id]);
$sizes = $stmt_sizes->fetchAll(PDO::FETCH_ASSOC);

// Проверка на авторизацию
$user_logged_in = isset($_SESSION['user_id']);

// Проверка, находится ли товар в избранном
$is_favorite = false;
if ($user_logged_in) {
    $user_id = $_SESSION['user_id'];
    $query_favorites = "SELECT * FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
    $stmt_favorites = $pdo->prepare($query_favorites);
    $stmt_favorites->execute([':user_id' => $user_id, ':product_id' => $product_id]);
    $is_favorite = $stmt_favorites->rowCount() > 0;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .product-page {
            display: flex;
            justify-content: space-around;
            padding: 40px;
            background-color: #f9f9f9;
        }
        .product-info {
            width: 50%;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .product-info h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .product-info p {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .product-photo {
            width: 40%;
            text-align: center;
        }
        .product-photo img {
            width: 100%;
            border-radius: 10px;
            max-width: 600px;
        }
        .sizes {
            margin: 10px 0;
            font-size: 16px;
        }
        .sizes span {
            display: inline-block;
            background-color: #ddd;
            padding: 10px 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .sizes span:hover {
            background-color: #e4c9f7;
        }
        .sizes span.selected {
            background-color: #e089f5;
        }
        .quantity-container {
            margin-top: 20px;
            display: flex;
            align-items: center;
        }
        .quantity-container input {
            width: 60px;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            text-align: center;
            margin: 0 10px;
        }
        .quantity-container .quantity-btn {
            padding: 10px;
            font-size: 16px;
            background-color: #ddd;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 40px; /* Выравнивание кнопок */
        }
        .quantity-container .quantity-btn:hover {
            background-color: #e4c9f7;
        }
        .buttons-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 18px;
            background-color: #e4c9f7;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #e089f5;
        }
        .btn.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .favorite-icon {
            cursor: pointer;
            width: 40px;
            height: 40px;
        }
        .favorite-icon img {
            width: 100%;
            height: 100%;
        }

        .notification {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #FF69B4;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
		
		.back-button {
			position: absolute;
			top: 20px;
			right: 20px;
			font-size: 24px;
			color: #e4c9f7; /* Цвет стрелочки */
			text-decoration: none;
			transition: color 0.3s ease;
		}

		.back-button:hover {
			color: #e089f5; /* Цвет при наведении */
		}

		.product-info {
			position: relative; /* Для того чтобы кнопка размещалась относительно блока .product-info */
		}


    </style>
</head>
<body>

<main>
    <div class="product-page">
	
	
        <div class="product-photo">
            <img src="<?= $product['photo'] ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
        </div>

        <div class="product-info">
		
            <h1><?= htmlspecialchars($product['product_name']) ?></h1>
			<!-- Кнопка "Назад" в правом верхнем углу -->
			<div class="back-button-container">
				<a href="javascript:history.back()" class="back-button">← Назад</a>
			</div>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p><strong>Цена:</strong> <?= number_format($product['price'], 2, ',', ' ') ?> ₽</p>
            
            <div class="sizes">
                <strong>Доступные размеры:</strong>
                <?php foreach ($sizes as $size): ?>
                    <span class="size-button" data-size="<?= htmlspecialchars($size['size_name']) ?>"><?= htmlspecialchars($size['size_name']) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="quantity-container">
                <label for="quantity">Количество:</label>
				
                <button class="quantity-btn" id="decrease">-</button>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="5">
                <button class="quantity-btn" id="increase">+</button>
            </div>

            <div class="buttons-container">
                <button class="btn <?= $user_logged_in ? '' : 'disabled' ?>" onclick="addToCart(<?= $product['product_id'] ?>)">В корзину</button>
				<!-- Сердечко для добавления в избранное -->
				<div class="favorite-icon" onclick="toggleFavorite(<?= $product['product_id'] ?>)">
					<img class="heart-icon" src="<?= $is_favorite ? 'heart_liked.svg' : 'heart_unliked.svg' ?>" alt="<?= $is_favorite ? 'В избранном' : 'Не в избранном' ?>">
				</div>
			</div>
			


            

        </div>
    </div>
</main>

<div id="notification" class="notification"></div>

<script>
function showNotification(message) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.style.display = 'block';
    setTimeout(() => { notification.style.display = 'none'; }, 3000);
}
let selectedSize = null;

// Обработка выбора размера
const sizeButtons = document.querySelectorAll('.size-button');
sizeButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Снимаем выделение с предыдущего размера
        sizeButtons.forEach(btn => btn.classList.remove('selected'));
        
        // Выделяем новый размер
        button.classList.add('selected');
        
        // Сохраняем выбранный размер
        selectedSize = button.dataset.size;
    });
});

document.getElementById('decrease').addEventListener('click', function() {
    const quantityInput = document.getElementById('quantity');
    let quantity = parseInt(quantityInput.value);
    if (quantity > 1) {
        quantityInput.value = quantity - 1;
    }
});

document.getElementById('increase').addEventListener('click', function() {
    const quantityInput = document.getElementById('quantity');
    let quantity = parseInt(quantityInput.value);
    if (quantity < 5) {  // Ограничение на количество товара
        quantityInput.value = quantity + 1;
    }
});

function addToCart(productId) {
    if (!selectedSize) {
        showNotification('Пожалуйста, выберите размер!');
        return;
    }

    if (!<?= json_encode($user_logged_in) ?>) {
        showNotification('Для добавления в корзину нужно войти в систему!');
        return;
    }

    const quantity = document.getElementById('quantity').value;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        const response = JSON.parse(xhr.responseText);
        showNotification(response.message);
    };
    xhr.send(`product_id=${productId}&quantity=${quantity}&size=${selectedSize}`);
}


function toggleFavorite(productId) {
    console.log('Нажали на сердечко, отправляем запрос');  // Добавлено для отладки
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'toggle_favorite.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log('Ответ от сервера:', xhr.responseText);  // Добавлено для отладки
            const heartIcon = document.querySelector('.heart-icon');
            const isLiked = heartIcon.src.includes('heart_liked');
            
            if (isLiked) {
                heartIcon.src = 'heart_unliked.svg';
                showNotification('Товар удален из избранного');
            } else {
                heartIcon.src = 'heart_liked.svg';
                showNotification('Товар добавлен в избранное');
            }
        }
    };
    xhr.send(`product_id=${productId}`);
}


</script>

</body>
</html>
