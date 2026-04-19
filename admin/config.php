<?php
// Подключение к базе данных
require_once '../config.php';

// Функция проверки авторизации админа
function checkAdminAuth() {
    session_start();
    if (!isset($_SESSION['admin'])) {
        header("Location: login.php");
        exit();
    }
    return $_SESSION['admin'];
}

// Функция для безопасной загрузки изображений
function uploadImage($file, $carId) {
    global $pdo;
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Проверка типа файла
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Недопустимый тип файла. Разрешены: JPG, PNG, WEBP, HEIC'];
    }
    
    // Проверка размера
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Размер файла превышает 5MB'];
    }
    
    // Генерация уникального имени
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('car_' . $carId . '_') . '.' . $extension;
    $uploadDir = '../uploads/cars/';
    
    // Создаем директорию, если ее нет
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'error' => 'Ошибка сервера: Не удалось создать директорию для загрузки файлов'];
        }
    }
    
    // Проверка прав на запись
    if (!is_writable($uploadDir)) {
        return ['success' => false, 'error' => 'Ошибка сервера: Папка загрузок недоступна для записи'];
    }
    
    $uploadPath = $uploadDir . $filename;
    
    // Загрузка файла
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Сохранение в БД
        $stmt = $pdo->prepare("INSERT INTO car_images (car_id, image_path, sort_order) VALUES (?, ?, ?)");
        
        // Получаем максимальный sort_order для этого автомобиля
        $stmtOrder = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM car_images WHERE car_id = ?");
        $stmtOrder->execute([$carId]);
        $nextOrder = $stmtOrder->fetch(PDO::FETCH_ASSOC)['next_order'];
        
        $stmt->execute([$carId, 'uploads/cars/' . $filename, $nextOrder]);
        
        return ['success' => true, 'filename' => $filename, 'id' => $pdo->lastInsertId()];
    }
    
    return ['success' => false, 'error' => 'Ошибка при загрузке файла'];
}

// Функция для удаления изображения
function deleteImage($imageId) {
    global $pdo;
    
    // Получаем путь к файлу
    $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image) {
        // Удаляем файл
        $filePath = '../' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Удаляем запись из БД
        $stmt = $pdo->prepare("DELETE FROM car_images WHERE id = ?");
        $stmt->execute([$imageId]);
        
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Изображение не найдено'];
}

// Функция для установки главного изображения
function setMainImage($imageId, $carId) {
    global $pdo;
    
    try {
        // Сбрасываем все is_main для этого автомобиля
        $stmt = $pdo->prepare("UPDATE car_images SET is_main = 0 WHERE car_id = ?");
        $stmt->execute([$carId]);
        
        // Устанавливаем новое главное изображение
        $stmt = $pdo->prepare("UPDATE car_images SET is_main = 1 WHERE id = ? AND car_id = ?");
        $stmt->execute([$imageId, $carId]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Функция для получения всех изображений автомобиля
function getCarImages($carId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_main DESC, sort_order ASC");
    $stmt->execute([$carId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
