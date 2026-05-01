<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'] ?? $_SESSION['admin']['id'] ?? 0;
$car_id = $_GET['car_id'] ?? '';
$return_url = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';

if (empty($car_id) || empty($user_id)) {
    $_SESSION['error'] = "Ошибка: автомобиль не указан";
    header("Location: " . $return_url);
    exit();
}

try {
    // Проверяем, есть ли уже этот автомобиль в избранном
    $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$user_id, $car_id]);
    $existing_favorite = $stmt->fetch();

    if ($existing_favorite) {
        // Удаляем из избранного
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND car_id = ?");
        $stmt->execute([$user_id, $car_id]);
        $_SESSION['success'] = "Автомобиль удален из избранного!";
    } else {
        // Добавляем в избранное
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, car_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $car_id]);
        $_SESSION['success'] = "Автомобиль добавлен в избранное!";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Ошибка при обновлении избранного: " . $e->getMessage();
}

header("Location: " . $return_url);
exit();
?>
