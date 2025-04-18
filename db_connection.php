<?php
// db_connection.php

// Параметры подключения к базе данных
$host = 'localhost';  // Хост базы данных
$dbname = 'shop_clothes_database';  // Имя базы данных
$username = 'root';  // Имя пользователя базы данных
$password = '';  // Пароль базы данных (оставить пустым для локальных серверов)

// Создание подключения с использованием PDO
try {
    // Устанавливаем соединение с базой данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Устанавливаем режим обработки ошибок для PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Установим кодировку на utf8 для поддержки русских символов
    $pdo->exec("SET NAMES 'utf8'");
    
    // Если соединение установлено, выводим сообщение для отладки
    // echo "Соединение с базой данных установлено!";
	
} catch (PDOException $e) {
    // В случае ошибки подключения выводим сообщение
    echo "Ошибка подключения: " . $e->getMessage();
    die();
}
?>
