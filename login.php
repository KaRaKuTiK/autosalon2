<?php
session_start();
require_once 'config.php';

// Получение данных из формы
$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

// Проверка пользователя
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
    $stmt->execute([$login, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Успешная авторизация
        $_SESSION['user'] = $user;
        $_SESSION['success'] = "Добро пожаловать, " . $user['full_name'] . "!";
        
        // Перенаправление на защищенную страницу
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Неверный логин или пароль";
        header("Location: dashboard.php");
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при авторизации: " . $e->getMessage();
    header("Location: index.php");
    exit();
}
?>