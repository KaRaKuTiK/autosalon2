<?php
require_once 'config.php';
$admin = checkAdminAuth();

$imageId = (int)($_GET['id'] ?? 0);
$carId = (int)($_GET['car'] ?? 0);

if ($imageId > 0 && $carId > 0) {
    $result = setMainImage($imageId, $carId);
    
    if ($result['success']) {
        $_SESSION['success'] = 'Главное фото установлено';
    } else {
        $_SESSION['error'] = 'Ошибка: ' . $result['error'];
    }
}

header("Location: car_edit.php?id=$carId");
exit();
?>
