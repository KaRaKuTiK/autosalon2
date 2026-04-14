<?php
session_start();
require_once 'config.php';

// Получение данных из формы
$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$full_name = $_POST['full_name'] ?? '';

// Проверка на уникальность логина
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $stmt->execute([$login]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Пользователь с таким логином уже существует";
        header("Location: register.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при проверке логина: " . $e->getMessage();
    header("Location: register.php");
    exit();
}

// Добавление пользователя в базу данных
try {
    $stmt = $pdo->prepare("INSERT INTO users (login, password, email, age, gender, full_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$login, $password, $email, $age, $gender, $full_name]);
    
    $_SESSION['success'] = "Регистрация прошла успешно! Теперь вы можете войти в систему.";
    header("Location: dashboard.php");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при регистрации: " . $e->getMessage();
    header("Location: register.php");
    exit();
}
?>