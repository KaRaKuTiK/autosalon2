<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$cart_id = $_GET['id'] ?? '';

if (empty($cart_id)) {
    $_SESSION['error'] = "Ошибка: элемент корзины не указан";
    header("Location: cart.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    $_SESSION['success'] = "Автомобиль удален из корзины!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при удалении из корзины: " . $e->getMessage();
}

header("Location: cart.php");
exit();
?>