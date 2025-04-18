<?php
session_start();
include('db_connection.php');
include('header.php');

// Проверка, был ли отправлен запрос на сохранение данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $city = $_POST['city'];
    $street = $_POST['street'];
    $house = $_POST['house'];
    $apartment = $_POST['apartment'];

    // Обновление данных пользователя в базе данных
    $stmt = $pdo->prepare("UPDATE Users SET first_name = ?, last_name = ?, email = ?, phone = ?, username = ?, city = ?, street = ?, house = ?, apartment = ? WHERE user_id = ?");
    $stmt->execute([$first_name, $last_name, $email, $phone, $username, $city, $street, $house, $apartment, $_SESSION['user_id']]);

    // Проверка и обработка изменения пароля
    if (isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Проверка, совпадает ли новый пароль с подтверждением
        if ($new_password === $confirm_password) {
            // Получаем текущий пароль из базы данных
            $stmt = $pdo->prepare("SELECT password FROM Users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            // Проверка, совпадает ли старый пароль
            if (password_verify($old_password, $user['password'])) {
                // Хешируем новый пароль
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Обновляем пароль в базе данных
                $update_stmt = $pdo->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
                $update_stmt->execute([$new_password_hash, $_SESSION['user_id']]);

                $message = "Пароль успешно изменен!";
            } else {
                $message = "Старый пароль неверен!";
            }
        } else {
            $message = "Новый пароль и подтверждение пароля не совпадают!";
        }
    }

    // Можете добавить уведомление, что данные были успешно обновлены
    $message = "Данные успешно обновлены!";
}

// Получение данных о пользователе из базы данных
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Получаем список заказов пользователя
    $orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
    $orders_stmt->execute([$user_id]);
    $orders = $orders_stmt->fetchAll();

    // Для каждого заказа получаем список товаров
    $order_items = [];
    foreach ($orders as $order) {
        $items_stmt = $pdo->prepare("SELECT a.product_id, a.product_name, a.price, oi.quantity 
                                    FROM order_items oi 
                                    JOIN assortment a ON oi.product_id = a.product_id 
                                    WHERE oi.order_id = ?");
        $items_stmt->execute([$order['order_id']]);
        $order_items[$order['order_id']] = $items_stmt->fetchAll();
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1 class="welcome">Добро пожаловать, <?php echo htmlspecialchars($user['first_name']); ?> <?php echo htmlspecialchars($user['last_name']); ?>!</h1>
    <hr>
    <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

		<form action="account.php" method="POST">
			<div class="edit-section">
				<div class="category">
					<h2>Данные о пользователе</h2>
					<div class="input-group">
						<label for="first_name">Имя:</label>
						<span id="first_name_display"><?php echo htmlspecialchars($user['first_name']); ?></span>
						<input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<label for="last_name">Фамилия:</label>
						<span id="last_name_display"><?php echo htmlspecialchars($user['last_name']); ?></span>
						<input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<label for="email">Электронная почта:</label>
						<span id="email_display"><?php echo htmlspecialchars($user['email']); ?></span>
						<input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<label for="phone">Телефон:</label>
						<span id="phone_display"><?php echo htmlspecialchars($user['phone']); ?></span>
						<input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="hidden">
					</div>
				</div>
				
				<div class="category">
					<h2>Данные об аккаунте</h2>
					<div class="input-group">
						<label for="username">Логин:</label>
						<span id="username_display"><?php echo htmlspecialchars($user['username']); ?></span>
						<input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<button type="button" class="btn-edit" onclick="togglePasswordChange()">Сменить пароль</button>
					</div>
					<div id="password_change" class="category hidden">
						<h2>Сменить пароль</h2>
						<div class="input-group">
							<label for="old_password">Старый пароль:</label>
							<input type="password" name="old_password" id="old_password">
						</div>
						<div class="input-group">
							<label for="new_password">Новый пароль:</label>
							<input type="password" name="new_password" id="new_password">
						</div>
						<div class="input-group">
							<label for="confirm_password">Подтвердите новый пароль:</label>
							<input type="password" name="confirm_password" id="confirm_password">
						</div>
					</div>
				</div>
				
				<div class="category">
					<h2>Адрес</h2>
					<div class="input-group">
						<label for="city">Город:</label>
						<span id="city_display"><?php echo htmlspecialchars($user['city']); ?></span>
						<input type="text" name="city" id="city" value="<?php echo htmlspecialchars($user['city']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<label for="street">Улица:</label>
						<span id="street_display"><?php echo htmlspecialchars($user['street']); ?></span>
						<input type="text" name="street" id="street" value="<?php echo htmlspecialchars($user['street']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<label for="house">Дом:</label>
						<span id="house_display"><?php echo htmlspecialchars($user['house']); ?></span>
						<input type="text" name="house" id="house" value="<?php echo htmlspecialchars($user['house']); ?>" class="hidden">
					</div>
					<div class="input-group">
						<label for="apartment">Квартира:</label>
						<span id="apartment_display"><?php echo htmlspecialchars($user['apartment']); ?></span>
						<input type="text" name="apartment" id="apartment" value="<?php echo htmlspecialchars($user['apartment']); ?>" class="hidden">
					</div>
				</div>
			</div>

			<div class="submit">
				<button class="btn-save" type="submit">Сохранить изменения</button>
				<button type="button" class="btn-edit" id="edit_button" onclick="toggleEdit()">Редактировать данные</button>
			</div>
			
			<div class="submit">
				<button type="button" class="btn-logout" onclick="logout()">Выйти из аккаунта</button>
			</div>
		</form>


        </div>
    </form>
</div>

<div class="container">
            <!-- Новый контейнер с заказами -->
            <div class="orders-section">
                <h2>Мои заказы</h2>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order">
                            <h3>Заказ #<?= htmlspecialchars($order['order_id']) ?> </h3>
                            <ul>
                                <?php foreach ($order_items[$order['order_id']] as $item): ?>
                                    <li>
                                        <a href="/product.php?id=<?=htmlspecialchars($item['product_id']) ?>"><?= htmlspecialchars($item['product_name']) ?></a> - 
                                        <?= $item['quantity'] ?> шт. по цене <?= number_format($item['price'], 2, ',', ' ') ?> ₽
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p>Итоговая сумма: <?= number_format(array_sum(array_map(function ($item) {
                                return $item['price'] * $item['quantity'];
                            }, $order_items[$order['order_id']])), 2, ',', ' ') ?> ₽</p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>У вас нет заказов.</p>
                <?php endif; ?>
            </div>
</div>


<script>
function toggleEdit() {
    const inputs = document.querySelectorAll('.category input');
    const spans = document.querySelectorAll('.category span');
    const button = document.getElementById('edit_button');

    // Переключаем видимость полей
    inputs.forEach(input => input.classList.toggle('hidden'));
    spans.forEach(span => span.classList.toggle('hidden'));

    // Меняем текст на кнопке
    if (button.textContent === 'Редактировать данные') {
        button.textContent = 'Отменить редактирование';
    } else {
        button.textContent = 'Редактировать данные';
    }
}

function togglePasswordChange() {
    const passwordChangeSection = document.getElementById('password_change');
    passwordChangeSection.classList.toggle('hidden');
}

function logout() {
    // Осуществляем выход из сессии
    window.location.href = 'logout.php'; // Перенаправляем пользователя на страницу logout.php
}


</script>

</body>
</html>


<style>


.container {
    width: 80%;
    max-width: 1200px; /* Увеличиваем ширину контейнера для большего пространства */
    margin: 50px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.welcome {
    text-align: center;
    font-size: 30px;
    margin-bottom: 30px;
    color: var(--dark-pink);
}

.category {
    margin-bottom: 20px;
    width: 32%; /* Каждому контейнеру по третьей части ширины */
    box-sizing: border-box;
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

.input-group span {
    display: inline-block;
    padding: 10px;
    background-color: #f3f3f3;
    border-radius: 20px;
    font-size: 16px;
    margin-bottom: 5px;
    width: 100%;
}

.input-group input {
    display: none;
    width: 100%;
    padding: 10px;
    border-radius: 20px;
    border: 1px solid #fadadd;
    font-size: 16px;
}

.input-group input.hidden {
    display: none;
}

.input-group input:not(.hidden) {
    display: inline-block;
}

.submit {
    text-align: center;
}

.btn-save, .btn-edit {
    padding: 10px 20px;
    background-color: #ff69b4;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.btn-save:hover, .btn-edit:hover {
    background-color: #d81b60;
}

.message {
    background-color: #e0f7fa;
    padding: 15px;
    margin-bottom: 20px;
    text-align: center;
    color: #00796b;
    border-radius: 5px;
}

.hidden {
    display: none;
}

.btn-logout {
    padding: 10px 20px;
    background-color: #ff69b4;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
    margin-top: 15px;
}

.btn-logout:hover {
    background-color: #f50057;
}

/* Флексбокс для выравнивания контейнеров по горизонтали */
.edit-section {
    display: flex;
    justify-content: space-between;
    gap: 20px; /* Расстояние между контейнерами */
}

@media (max-width: 768px) {
    /* При меньших экранах делаем контейнеры вертикальными */
    .edit-section {
        flex-direction: column;
    }

    .category {
        width: 100%;
    }
}

.orders-section {
    margin-top: 30px;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.order {
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.order h3 {
    font-size: 18px;
    color: var(--dark-pink);
}

.order ul {
    list-style-type: none;
    padding: 0;
}

.order li {
    font-size: 16px;
    margin-bottom: 5px;
}

.order p {
    font-weight: bold;
    color: #ff69b4;
}

</style>