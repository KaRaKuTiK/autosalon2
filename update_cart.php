<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$cart_id = $_POST['cart_id'] ?? '';
$quantity = $_POST['quantity'] ?? 1;

if (empty($cart_id)) {
    $_SESSION['error'] = "Ошибка: элемент корзины не указан";
    header("Location: cart.php");
    exit();
}

if ($quantity < 1) {
    // Если количество меньше 1, удаляем элемент
    header("Location: remove_from_cart.php?id=" . $cart_id);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $user_id]);
    
    $_SESSION['success'] = "Корзина обновлена!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при обновлении корзины: " . $e->getMessage();
}

header("Location: cart.php");
exit();
?>