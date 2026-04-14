<?php
require_once 'config.php';

try {
    // Пытаемся добавить колонку role, если её нет
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
        echo "Колонка role добавлена.<br>";
    }

    // Проверяем, существует ли пользователь 'admin'
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = 'admin'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        // Создаем администратора
        // Kacher73_dd
        $hash = password_hash('Kacher73_dd', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (login, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $hash, 'admin@autosalon.local', 'Администратор', 'admin']);
        echo "Администратор 'admin' с захешированным паролем успешно создан.<br>";
    } else {
        $hash = password_hash('Kacher73_dd', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE login = 'admin'");
        $stmt->execute([$hash]);
        echo "Пользователь 'admin' уже существует. Данные (пароль и роль) были обновлены.<br>";
    }
} catch (PDOException $e) {
    echo "Ошибка БД: " . $e->getMessage() . "<br>";
}
echo "<br><a href='index.php'>Вернуться на главную</a>";
?>
