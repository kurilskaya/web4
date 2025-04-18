<?php
session_start();

// Завершаем сессию
session_unset();
session_destroy();

// Перенаправляем на страницу авторизации
header('Location: login.php');
exit();
?>
