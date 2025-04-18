<?php
session_start();
include('db_connection.php'); // Подключаем PDO
include('header.php');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем данные из таблицы заказов для текущего пользователя
$order_id = $_GET['order_id']; // Получаем order_id из URL, переданный после оформления заказа

// 1. Получаем информацию о заказе (необязательно, можно отобразить в подтверждении)
$query = "SELECT * FROM orders WHERE order_id = :order_id AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Ошибка: заказ не найден!";
    exit();
}

// 2. Получаем информацию о товарах, которые были в заказе
$query = "SELECT oi.product_id, oi.quantity, oi.size_id, p.product_name, p.price 
          FROM order_items oi 
          JOIN assortment p ON oi.product_id = p.product_id 
          WHERE oi.order_id = :order_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Выводим информацию о заказе
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение заказа</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="order-confirmation">
        <h2>Ваш заказ был оформлен!</h2>
        
        <p>Номер заказа: <?= htmlspecialchars($order['order_id']) ?></p>
        
        <h3>Товары в заказе:</h3>
        <ul>
            <?php foreach ($order_items as $item): ?>
                <li>
                    <?= htmlspecialchars($item['product_name']) ?> (Размер: <?= htmlspecialchars($item['size_id']) ?>) 
                    - <?= $item['quantity'] ?> шт. 
                    по цене <?= number_format($item['price'], 2, ',', ' ') ?> ₽
                </li>
            <?php endforeach; ?>
        </ul>

        <p>Итоговая сумма: <?= number_format(array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $order_items)), 2, ',', ' ') ?> ₽</p>

        <a href="index.php">Перейти на главную</a>
    </div>
</body>
</html>