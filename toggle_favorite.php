<?php
session_start();
include('db_connection.php');

// Проверяем, что пользователь авторизован
if (isset($_SESSION['user_id']) && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];

    // Проверяем, есть ли уже этот товар в избранном
    $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);

    if ($stmt->rowCount() > 0) {
        // Если товар в избранном, удаляем его
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
    } else {
        // Если товара нет в избранном, добавляем его
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id) VALUES (:user_id, :product_id)");
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
    }

    echo 'Success';
}
?>
