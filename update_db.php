<?php
require_once 'config.php';

try {
    // Добавление колонки avatar
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
        echo "Колонка avatar добавлена успешно.<br>";
    } else {
        echo "Колонка avatar уже существует.<br>";
    }

    // Сброс имени для admin, если оно "Администратор", чтобы запросить его при входе
    $stmt = $pdo->prepare("UPDATE users SET full_name = '' WHERE login = 'admin' AND full_name = 'Администратор'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "Имя администратора сброшено (нужно заполнить при входе).<br>";
    } else {
        echo "Имя администратора не было изменено (либо уже изменено, либо не равно 'Администратор').<br>";
    }

} catch (PDOException $e) {
    echo "Ошибка БД: " . $e->getMessage() . "<br>";
}
echo "<br><a href='index.php'>Вернуться на главную</a>";
?>
