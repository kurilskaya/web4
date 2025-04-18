<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['size'])) {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Вы не авторизованы!']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $size_name = $_POST['size'];
    $quantity = $_POST['quantity'] ?? 1;

    // Получаем size_id по названию размера
    $query_size = "SELECT size_id FROM sizes WHERE size_name = :size_name";
    $stmt_size = $pdo->prepare($query_size);
    $stmt_size->execute([':size_name' => $size_name]);
    $size = $stmt_size->fetch(PDO::FETCH_ASSOC);

    if (!$size) {
        echo json_encode(['status' => 'error', 'message' => 'Неверный размер!']);
        exit;
    }

    $size_id = $size['size_id'];

    // Проверяем, есть ли уже такой товар в корзине пользователя
    $query_check = "SELECT quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id AND size_id = :size_id";
    $stmt_check = $pdo->prepare($query_check);
    $stmt_check->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id,
        ':size_id' => $size_id
    ]);
    $existing_item = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($existing_item) {
        // Если товар уже есть в корзине, увеличиваем количество
        $new_quantity = $existing_item['quantity'] + $quantity;
        $query_update = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id AND size_id = :size_id";
        $stmt_update = $pdo->prepare($query_update);
        $stmt_update->execute([
            ':quantity' => $new_quantity,
            ':user_id' => $user_id,
            ':product_id' => $product_id,
            ':size_id' => $size_id
        ]);
    } else {
        // Если товара нет, добавляем его в корзину
        $query_insert = "INSERT INTO cart (user_id, product_id, size_id, quantity) VALUES (:user_id, :product_id, :size_id, :quantity)";
        $stmt_insert = $pdo->prepare($query_insert);
        $stmt_insert->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id,
            ':size_id' => $size_id,
            ':quantity' => $quantity
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Товар добавлен в корзину!']);
    exit;
}
?>
