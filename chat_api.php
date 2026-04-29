<?php
/**
 * chat_api.php — API для онлайн-чата
 * Действия: init_session, send_message, get_messages, close_session
 */
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── ИНИЦИАЛИЗАЦИЯ СЕССИИ ─────────────────────────────────────────────────
if ($action === 'init_session') {
    $token = bin2hex(random_bytes(32));
    $userId = null;
    $guestName = 'Гость';
    $guestEmail = '';

    if (isset($_SESSION['user'])) {
        $userId = $_SESSION['user']['id'];
        $guestName = $_SESSION['user']['full_name'];
        $guestEmail = $_SESSION['user']['email'] ?? '';
    } else {
        $guestName = trim($_POST['guest_name'] ?? 'Гость');
        $guestEmail = trim($_POST['guest_email'] ?? '');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO chat_sessions (session_token, user_id, guest_name, guest_email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$token, $userId, $guestName, $guestEmail]);
        $sessionId = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'token' => $token, 'session_id' => $sessionId, 'name' => $guestName]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── ОТПРАВКА СООБЩЕНИЯ ───────────────────────────────────────────────────
if ($action === 'send_message') {
    $token   = trim($_POST['token'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($token)) {
        echo json_encode(['success' => false, 'error' => 'Нет токена сессии']);
        exit;
    }

    // Ищем сессию
    $stmt = $pdo->prepare("SELECT * FROM chat_sessions WHERE session_token = ? AND status = 'open'");
    $stmt->execute([$token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        echo json_encode(['success' => false, 'error' => 'Сессия не найдена или закрыта']);
        exit;
    }

    $filePath = null;
    $fileName = null;
    $fileType = null;

    // Обработка файла
    if (!empty($_FILES['file']['tmp_name'])) {
        $file = $_FILES['file'];
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        $maxSize = 10 * 1024 * 1024; // 10 MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Недопустимый тип файла']);
            exit;
        }
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'Файл слишком большой (макс. 10 МБ)']);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'chat_' . uniqid() . '.' . $ext;
        $uploadDir = 'uploads/chat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            $filePath = $uploadDir . $newName;
            $fileName = $file['name'];
            $fileType = $mime;
        }
    }

    if (empty($message) && !$filePath) {
        echo json_encode(['success' => false, 'error' => 'Пустое сообщение']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message, file_path, file_name, file_type) VALUES (?, 'user', ?, ?, ?, ?)");
        $stmt->execute([$session['id'], $message ?: null, $filePath, $fileName, $fileType]);

        // Обновляем updated_at сессии
        $pdo->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = ?")->execute([$session['id']]);

        $msgId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message_id' => $msgId]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── ПОЛУЧЕНИЕ СООБЩЕНИЙ ──────────────────────────────────────────────────
if ($action === 'get_messages') {
    $token   = trim($_GET['token'] ?? '');
    $after   = (int)($_GET['after'] ?? 0); // last_message_id

    if (empty($token)) {
        echo json_encode(['success' => false, 'error' => 'Нет токена']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM chat_sessions WHERE session_token = ?");
    $stmt->execute([$token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        echo json_encode(['success' => false, 'error' => 'Сессия не найдена']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
    $stmt->execute([$session['id'], $after]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Помечаем сообщения от админа как прочитанные
    if (!empty($messages)) {
        $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender = 'admin' AND is_read = 0")->execute([$session['id']]);
    }

    // Получаем инфо об админе (для отображения аватара и имени)
    $stmtAdmin = $pdo->prepare("SELECT full_name, avatar FROM users WHERE role = 'admin' LIMIT 1");
    $stmtAdmin->execute();
    $adminInfo = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

    // Получаем максимальный прочитанный ID
    $readUpTo = $pdo->prepare("SELECT MAX(id) FROM chat_messages WHERE session_id = ? AND sender = 'user' AND is_read = 1");
    $readUpTo->execute([$session['id']]);
    $readUpToId = $readUpTo->fetchColumn();

    echo json_encode(['success' => true, 'messages' => $messages, 'status' => $session['status'], 'admin_info' => $adminInfo, 'read_up_to' => $readUpToId]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
