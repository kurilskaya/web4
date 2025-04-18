<?php
session_start();
include('db_connection.php');
include('header.php');

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные о товарах в корзине
$query_cart = "
    SELECT c.product_id, c.size_id, c.quantity, p.product_name, p.price, p.photo, s.size_name 
    FROM cart c
    JOIN assortment p ON c.product_id = p.product_id
    JOIN sizes s ON c.size_id = s.size_id
    WHERE c.user_id = :user_id
";
$stmt_cart = $pdo->prepare($query_cart);
$stmt_cart->execute([':user_id' => $user_id]);
$cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

// Получаем данные пользователя для автозаполнения формы доставки
$query_user = "
    SELECT first_name, last_name, email, phone, city, street, house, apartment 
    FROM users WHERE user_id = :user_id
";
$stmt_user = $pdo->prepare($query_user);
$stmt_user->execute([':user_id' => $user_id]);
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Если корзина пуста
if (empty($cart_items)) {
    echo "
<style>
	.empty-cart {
		display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            font-size: 28px;
            color: #fff;
            width: auto; 
            max-width: 450px; 
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

	.empty-cart .btn {
		display: inline-block;
		padding: 16px 40px; /* больше размер */
		font-size: 20px; /* чуть больше текст */
		color: white;
		background-color: #ff69b4; /* нежно-розовый */
		border: none;
		border-radius: 35px;
		text-decoration: none;
		transition: background-color 0.3s ease, transform 0.2s ease;
		box-shadow: 0 6px 18px rgba(255, 105, 180, 0.4);
		font-weight: bold;
		letter-spacing: 0.5px;
	}

	.empty-cart .btn:hover {
		background-color: #ff4fa1; /* чуть темнее при наведении */
		transform: scale(1.05); /* плавное увеличение при наведении */
	}

body {
	background-color: #f8f8f8; /* Белый фон на всю страницу */
	position: relative;
}

body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/logo.png') no-repeat center center;
            background-size: contain;
            opacity: 0.1; /* Полупрозрачный фон */
            z-index: -1; /* Логотип будет за всеми контейнерами */
        }
	
</style>
	<div class='empty-cart'>
            <p>Ваша корзина пуста</p>
            <a href='catalog.php' class='btn'>Перейти в каталог</a>
          </div>";
    exit;
}

// Обработка удаления товара
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    $size_id = $_GET['size_id'];

    // Удаляем товар из корзины
    $query_delete = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id AND size_id = :size_id";
    $stmt_delete = $pdo->prepare($query_delete);
    $stmt_delete->execute([':user_id' => $user_id, ':product_id' => $product_id, ':size_id' => $size_id]);

    // Перенаправляем обратно на страницу корзины
    header('Location: cart.php');
    exit;
}

// Обработка изменения количества товара
if (isset($_POST['update'])) {
    $product_id = $_POST['product_id'];
    $size_id = $_POST['size_id'];
    $quantity = $_POST['quantity'];

    // Обновляем количество товара в корзине, ограничиваем максимум 5
    if ($quantity > 5) {
        $quantity = 5;
    }

    // Обновляем количество товара в корзине
    $query_update = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id AND size_id = :size_id";
    $stmt_update = $pdo->prepare($query_update);
    $stmt_update->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id,
        ':size_id' => $size_id,
        ':quantity' => $quantity
    ]);

    // Перенаправляем обратно на страницу корзины
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <div class="cart-container">
        <h1 class="cart-header">Ваша корзина</h1>

        <?php $total_price = 0; ?>
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <img src="<?= $item['photo'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                <div class="item-info">
                    <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                    <p>Размер: <?= htmlspecialchars($item['size_name']) ?></p>
                    <p>Цена: <?= number_format($item['price'], 2, ',', ' ') ?> ₽</p>
                    <p>Количество: <?= $item['quantity'] ?></p>
                    <p class="item-total-price">Итоговая цена: <?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> ₽</p>
                </div>
                <div class="cart-actions">
                    <form action="cart.php" method="POST">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="5">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <input type="hidden" name="size_id" value="<?= $item['size_id'] ?>">
                        <button type="submit" name="update" class="update-btn">Обновить</button>
                    </form>
                    <a href="cart.php?remove=<?= $item['product_id'] ?>&size_id=<?= $item['size_id'] ?>" class="remove-btn">Удалить</a>
                </div>
            </div>
            <?php $total_price += $item['price'] * $item['quantity']; ?>
        <?php endforeach; ?>

        <div class="form-container">
            <h2>Данные для доставки</h2>
            <form action="checkout.php" method="POST">
			<div class="delivery-details">
				<div class="personal-info">
					<label for="first_name">Имя:</label>
					<input type="text" name="first_name" value="<?= htmlspecialchars($user_data['first_name']) ?>" required>
					
					<label for="last_name">Фамилия:</label>
					<input type="text" name="last_name" value="<?= htmlspecialchars($user_data['last_name']) ?>" required>
					
					<label for="email">Email:</label>
					<input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
					
					<label for="phone">Телефон:</label>
					<input type="text" name="phone" value="<?= htmlspecialchars($user_data['phone']) ?>" required>
				</div>
				<div class="address-info">
					<label for="city">Город:</label>
					<input type="text" name="city" value="<?= htmlspecialchars($user_data['city']) ?>" required>
					
					<label for="street">Улица:</label>
					<input type="text" name="street" value="<?= htmlspecialchars($user_data['street']) ?>" required>
					
					<label for="house">Дом:</label>
					<input type="text" name="house" value="<?= htmlspecialchars($user_data['house']) ?>" required>
					
					<label for="apartment">Квартира:</label>
					<input type="text" name="apartment" value="<?= htmlspecialchars($user_data['apartment']) ?>" required>
				</div>
			</div>
			<button type="submit" name="place_order">Оформить заказ</button>
		</form>
        </div>

        <div class="total-price">
            <h2>Итоговая сумма: <?= number_format($total_price, 2, ',', ' ') ?> ₽</h2>
        </div>
    </div>
</main>

</body>
</html>


<style>
.cart-container {
    width: 80%;
    max-width: 800px;
    margin: 40px auto;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.cart-header {
    font-size: 32px;
    margin-bottom: 20px;
    color: var(--dark-pink);
}

.cart-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.cart-item img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
}

.item-info {
    flex: 1;
    padding: 0 15px;
}

.item-info h3 {
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}

.item-info p {
    margin: 5px 0;
    font-size: 16px;
}

.item-total-price {
    font-size: 16px;
    color: #e089f5;
    font-weight: bold;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cart-actions input {
    width: 60px;
    padding: 5px;
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #ddd;
    text-align: center;
    margin-bottom: 10px;
}

.cart-actions .update-btn,
.cart-actions .remove-btn {
    padding: 10px;
    font-size: 16px;
    background-color: #e4c9f7;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 5px;
}

.cart-actions .update-btn:hover,
.cart-actions .remove-btn:hover {
    background-color: #e089f5;
}

.remove-btn {
    color: red;
    font-weight: bold;
}

.total-price {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    color: #e4c9f7;
    margin-top: 30px;
}

.form-container {
    margin-top: 30px;
}

.delivery-details {
    display: flex;
    justify-content: space-between;
}

.personal-info,
.address-info {
    width: 45%;
}

label {
    font-weight: bold;
}

input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ddd;
}



</style>