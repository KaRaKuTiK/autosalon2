<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit();
}

// Защита от CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Ошибка токена безопасности. Попробуйте еще раз.";
    header("Location: admin_login.php");
    exit();
}

// Получение и экранирование данных
$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

// Защита от SQL-инъекций обеспечивается через использование подготовленных выражений PDO
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? AND role = 'admin'");
    $stmt->execute([$login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Верификация захешированного пароля
    if ($admin && password_verify($password, $admin['password'])) {
        // Успешная авторизация админа
        $_SESSION['user'] = $admin; // Для каталога
        $_SESSION['admin'] = $admin; // Для админ-панели
        
        header("Location: admin/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Неверные данные для входа";
        header("Location: admin_login.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка БД: " . $e->getMessage();
    header("Location: admin_login.php");
    exit();
}
?>
