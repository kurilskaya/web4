<?php
session_start();
include('db_connection.php'); // Здесь подключение с PDO

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем данные формы (для вывода, но не сохраняем в БД)
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$city = $_POST['city'];
$street = $_POST['street'];
$house = $_POST['house'];
$apartment = $_POST['apartment'];

// 1. Создаём заказ
$query = "INSERT INTO orders (user_id) VALUES (:user_id)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$order_id = $pdo->lastInsertId();  // Получаем ID нового заказа

// 2. Переносим товары из корзины в order_items
$query = "SELECT * FROM cart WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
    $product_id = $row['product_id'];
    $quantity = $row['quantity'];
    $size_id = $row['size_id'];

    $insert = "INSERT INTO order_items (order_id, product_id, quantity, size_id) VALUES (:order_id, :product_id, :quantity, :size_id)";
    $stmt_insert = $pdo->prepare($insert);
    $stmt_insert->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt_insert->bindParam(':size_id', $size_id, PDO::PARAM_INT);
    $stmt_insert->execute();
}

// 3. Очищаем корзину
$delete = "DELETE FROM cart WHERE user_id = :user_id";
$stmt = $pdo->prepare($delete);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

// 4. Сохраняем данные доставки во временные переменные сессии (для order_confirmation)
$_SESSION['delivery_info'] = [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'phone' => $phone,
    'city' => $city,
    'street' => $street,
    'house' => $house,
    'apartment' => $apartment
];

// 5. Перенаправляем
header("Location: order_confirmation.php?order_id=" . $order_id);
exit();
?>