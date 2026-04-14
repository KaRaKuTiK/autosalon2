<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$car_id = $_POST['car_id'] ?? '';

if (empty($car_id)) {
    $_SESSION['error'] = "Ошибка: автомобиль не указан";
    header("Location: dashboard.php");
    exit();
}

try {
    // Проверяем, есть ли уже этот автомобиль в корзине пользователя
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$user_id, $car_id]);
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        // Если уже есть, увеличиваем количество
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND car_id = ?");
        $stmt->execute([$user_id, $car_id]);
    } else {
        // Если нет, добавляем новый элемент
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, car_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $car_id]);
    }

    $_SESSION['success'] = "Автомобиль добавлен в корзину!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при добавлении в корзину: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();
?>