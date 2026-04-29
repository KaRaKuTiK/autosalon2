<?php
/**
 * admin/chat_api.php — API для админской части чата
 */
require_once 'config.php';
checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── СПИСОК СЕССИЙ ────────────────────────────────────────────────────────
if ($action === 'get_sessions') {
    try {
        $stmt = $pdo->query("
            SELECT cs.*,
                   (SELECT COUNT(*) FROM chat_messages WHERE session_id = cs.id AND is_read = 0 AND sender = 'user') as unread_count,
                   (SELECT message FROM chat_messages WHERE session_id = cs.id ORDER BY id DESC LIMIT 1) as last_message,
                   (SELECT created_at FROM chat_messages WHERE session_id = cs.id ORDER BY id DESC LIMIT 1) as last_message_time
            FROM chat_sessions cs
            ORDER BY cs.updated_at DESC
        ");
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'sessions' => $sessions]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── СООБЩЕНИЯ КОНКРЕТНОЙ СЕССИИ ──────────────────────────────────────────
if ($action === 'get_messages') {
    $sessionId = (int)($_GET['session_id'] ?? 0);
    $after = (int)($_GET['after'] ?? 0);

    if (!$sessionId) {
        echo json_encode(['success' => false, 'error' => 'Нет session_id']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
        $stmt->execute([$sessionId, $after]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Помечаем сообщения пользователя как прочитанные
        $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender = 'user' AND is_read = 0")->execute([$sessionId]);

        // Получаем инфо о сессии
        $sess = $pdo->prepare("SELECT * FROM chat_sessions WHERE id = ?");
        $sess->execute([$sessionId]);
        $sessionInfo = $sess->fetch(PDO::FETCH_ASSOC);

        // Получаем максимальный прочитанный ID из сообщений админа
        $readUpTo = $pdo->prepare("SELECT MAX(id) FROM chat_messages WHERE session_id = ? AND sender = 'admin' AND is_read = 1");
        $readUpTo->execute([$sessionId]);
        $readUpToId = $readUpTo->fetchColumn();

        echo json_encode(['success' => true, 'messages' => $messages, 'session' => $sessionInfo, 'read_up_to' => $readUpToId]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── ОТПРАВКА ОТВЕТА АДМИНИСТРАТОРОМ ─────────────────────────────────────
if ($action === 'send_reply') {
    $sessionId = (int)($_POST['session_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!$sessionId) {
        echo json_encode(['success' => false, 'error' => 'Нет session_id']);
        exit;
    }
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Пустое сообщение']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'admin', ?)");
        $stmt->execute([$sessionId, $message]);

        $pdo->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = ?")->execute([$sessionId]);

        echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── ЗАКРЫТИЕ СЕССИИ ─────────────────────────────────────────────────────
if ($action === 'close_session') {
    $sessionId = (int)($_POST['session_id'] ?? 0);
    if (!$sessionId) {
        echo json_encode(['success' => false, 'error' => 'Нет session_id']);
        exit;
    }

    try {
        $pdo->prepare("UPDATE chat_sessions SET status = 'closed' WHERE id = ?")->execute([$sessionId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── ОБЩЕЕ КОЛИЧЕСТВО НЕПРОЧИТАННЫХ ──────────────────────────────────────
if ($action === 'get_unread_count') {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM chat_messages WHERE sender = 'user' AND is_read = 0")->fetchColumn();
        echo json_encode(['success' => true, 'count' => (int)$count]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'count' => 0]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
