<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Необходимо авторизоваться']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $salon_address = trim($_POST['salon_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $view_date = trim($_POST['view_date'] ?? '');
    $view_time = trim($_POST['view_time'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $wishes = trim($_POST['wishes'] ?? '');

    // Server-side validation
    if (empty($full_name) || empty($salon_address) || empty($phone) || empty($view_date) || empty($view_time) || empty($payment_method)) {
        echo json_encode(['status' => 'error', 'message' => 'Заполните все обязательные поля']);
        exit();
    }

    // ХSS защита
    $full_name = htmlspecialchars($full_name);
    $salon_address = htmlspecialchars($salon_address);
    $phone = htmlspecialchars($phone);
    $wishes = htmlspecialchars($wishes);

    try {
        $stmt = $pdo->prepare("INSERT INTO test_drive_requests (full_name, salon_address, phone, view_date, view_time, payment_method, wishes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $salon_address, $phone, $view_date, $view_time, $payment_method, $wishes]);
        
        // TODO: Заглушка отправки email
        // mail("admin@autosalon.ru", "Новая запись на просмотр", "Запись от $full_name на $view_date $view_time. Телефон: $phone");
        
        // TODO: Заглушка Telegram бота
        // file_get_contents("https://api.telegram.org/bot<TOKEN>/sendMessage?chat_id=<ID>&text=" . urlencode("Новая запись: $full_name ($phone)"));
        
        echo json_encode(['status' => 'success', 'message' => 'Заявка на просмотр отправлена. Мы свяжемся с вами']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный запрос']);
}
?>
