


<?php

// Ввод пароля
$password = "user123"; // Здесь можно задать любой пароль

// Хэширование пароля с использованием алгоритма BCRYPT
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Вывод хэша пароля
echo "Хэш пароля: " . $hashedPassword;


// Введенный пароль (например, с формы)
$enteredPassword = "user123";

// Хэшированный пароль из базы данных
$hashedPasswordFromDB = '$2y$10$svzDzHMoQDqj7lRZ033S1.nFXbCzOSyibe2HE2IVuedUiQZ41VXwm'; // Пример хэша из базы данных

// Проверка пароля с помощью password_verify
if (password_verify($enteredPassword, $hashedPasswordFromDB)) {
    echo "Пароль верный!";
} else {
    echo "Неверный пароль!";
}
?>
