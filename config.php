<?php
$host = 'localhost';
$dbname = 'autosalon';
$username = 'root'; // стандартный пользователь XAMPP/OpenServer
$password = '';     // OpenServer обычно использует пустой пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>