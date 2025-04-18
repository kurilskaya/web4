<?php
session_start();
include('db_connection.php'); // Подключаем файл с подключением к базе данных
include('header.php');

// Проверка, если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    header('Location: account.php'); // Перенаправление на главную страницу
    exit;
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка на наличие пользователя в базе данных
    $query = "SELECT * FROM users WHERE username = :username";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверка пароля
            if (password_verify($password, $user['password'])) {
                // Успешный вход, сохраняем данные в сессии
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header('Location: account.php'); // Перенаправление на главную страницу
                exit;
            } else {
                $error = "Неверный пароль!";
            }
        } else {
            $error = "Пользователь не найден!";
        }
    } catch (PDOException $e) {
        $error = "Ошибка выполнения запроса: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">  <!-- Подключаем CSS файл -->
</head>
<body>
<div class="login-main">
    <div class="login-container">
        <div class="login-form">
            <h2>Авторизация</h2>

            <!-- Форма для авторизации -->
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Логин:</label>
                    <input type="text" id="username" name="username" required placeholder="Введите логин">
                </div>
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required placeholder="Введите пароль">
                </div>
                <button type="submit">Войти</button>
            </form>

            <!-- Ошибка входа -->
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </div>
</div>
</body>
</html>

<?php include('footer.php'); ?>
