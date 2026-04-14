<?php
require_once 'config.php';
$admin = checkAdminAuth();

$imageId = (int)($_GET['id'] ?? 0);
$carId = (int)($_GET['car'] ?? 0);

if ($imageId > 0) {
    $result = deleteImage($imageId);
    
    if ($result['success']) {
        $_SESSION['success'] = 'Фото успешно удалено';
    } else {
        $_SESSION['error'] = 'Ошибка: ' . $result['error'];
    }
}

header("Location: car_edit.php?id=$carId");
exit();
?>
