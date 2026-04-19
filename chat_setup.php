<?php
require_once 'config.php';

// Создаём таблицы для чата
$sql = "
CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(64) NOT NULL UNIQUE,
    user_id INT NULL,
    guest_name VARCHAR(100) DEFAULT 'Гость',
    guest_email VARCHAR(150) DEFAULT NULL,
    status ENUM('open','closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    sender ENUM('user','admin') NOT NULL DEFAULT 'user',
    message TEXT DEFAULT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    file_name VARCHAR(255) DEFAULT NULL,
    file_type VARCHAR(100) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
);
";

try {
    // MySQL не всегда поддерживает множественные запросы через exec, делаем по одному
    $queries = [
        "CREATE TABLE IF NOT EXISTS chat_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_token VARCHAR(64) NOT NULL UNIQUE,
            user_id INT NULL,
            guest_name VARCHAR(100) DEFAULT 'Гость',
            guest_email VARCHAR(150) DEFAULT NULL,
            status ENUM('open','closed') DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL,
            sender ENUM('user','admin') NOT NULL DEFAULT 'user',
            message TEXT DEFAULT NULL,
            file_path VARCHAR(500) DEFAULT NULL,
            file_name VARCHAR(255) DEFAULT NULL,
            file_type VARCHAR(100) DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
        )"
    ];
    foreach ($queries as $q) {
        $pdo->exec($q);
    }
    echo "Таблицы чата созданы успешно!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
