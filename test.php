<?php
$url = 'add_to_cart.php'; // Укажите правильный URL
$data = [
    'product_id' => 123, // ID товара
    'size_id' => 2 // ID размера
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo "Ошибка при отправке запроса.";
} else {
    echo $response; // Ответ от сервера
}
?>
