<?php
include('db_connection.php');
include('header.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $street = $_POST['street'];
    $house = $_POST['house'];
    $apartment = $_POST['apartment'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Хэшируем пароль

    // Вставка данных в таблицу Users
    $query = "INSERT INTO Users (username, password, first_name, last_name, email, phone, city, street, house, apartment) 
              VALUES (:username, :password, :first_name, :last_name, :email, :phone, :city, :street, :house, :apartment)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':phone' => $phone,
        ':city' => $city,
        ':street' => $street,
        ':house' => $house,
        ':apartment' => $apartment
    ]);

    // Перенаправление на страницу авторизации или входа после успешной регистрации
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ClothesForU</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            padding: 15px;
        }
        .register-form {
            width: 100%;
            max-width: 800px; 
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px; 
        }
        .register-form h1 {
            text-align: center;
            font-size: 30px;
            margin-bottom: 20px;
            color: var(--dark-pink);
            grid-column: span 2;
        }
        .form-group {
            margin-bottom: 10px;  
        }
        .form-group label {
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-group input:focus {
            border-color: #e4c9f7;
        }
        .form-group .username-password-container {
            grid-column: span 2;
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .form-group .username-password-container input {
            margin-top: 10px;
            width: 80%;  /* Сужаю поля для логина и пароля */
            margin-left: auto;
            margin-right: auto;
        }
        .register-btn {
            width: 100%;
            padding: 12px;
            background-color: #e4c9f7;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            grid-column: span 2;
        }
        .register-btn:hover {
            background-color: #e089f5;
        }
        .register-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Стили для выделенной надписи "Придумайте логин и пароль" */
        .username-password-container label {
            font-size: 18px;
            color: var(--dark-pink);
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<main>
    <div class="register-container">
        <form class="register-form" method="POST" action="register.php">
            <h1>Регистрация</h1>

            <div class="form-group">
                <label for="first_name">Имя:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Фамилия:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>

            <div class="form-group">
                <label for="email">Электронная почта:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="city">Город:</label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-group">
                <label for="street">Улица:</label>
                <input type="text" id="street" name="street" required>
            </div>

            <div class="form-group">
                <label for="house">Дом:</label>
                <input type="text" id="house" name="house" required>
            </div>

            <div class="form-group">
                <label for="apartment">Квартира:</label>
                <input type="text" id="apartment" name="apartment" required>
            </div>

            <div class="form-group username-password-container">
                <label for="username">Придумайте логин и пароль для входа</label>
                <input type="text" id="username" name="username" placeholder="Логин" required>
                <input type="password" id="password" name="password" placeholder="Пароль" required>
            </div>

            <button type="submit" class="register-btn">Зарегистрироваться</button>
        </form>
    </div>
</main>

</body>
</html>
