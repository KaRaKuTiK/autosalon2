<?php
require_once 'config.php';

$queries = [
    // 1. Добавление колонки is_blocked в таблицу users (если ее нет)
    "ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) DEFAULT 0",
    
    // 2. Создание таблицы salons
    "CREATE TABLE IF NOT EXISTS salons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        email VARCHAR(100),
        working_hours VARCHAR(100)
    )",

    // 3. Создание таблицы inventory
    "CREATE TABLE IF NOT EXISTS inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        salon_id INT NOT NULL,
        quantity INT DEFAULT 0,
        price DECIMAL(10,2) DEFAULT NULL,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        UNIQUE KEY car_salon (car_id, salon_id)
    )"
];

foreach ($queries as $index => $sql) {
    try {
        $pdo->exec($sql);
        echo "Запрос " . ($index + 1) . " успешно выполнен.\n";
    } catch (PDOException $e) {
        // Ошибка 1060: Duplicate column name = это нормально, если колонка уже есть (для MariaDB/MySQL старых версий, которые не поддерживают IF NOT EXISTS для ALTER TABLE)
        if ($e->errorInfo[1] == 1060) {
            echo "Запрос " . ($index + 1) . ": Колонка уже существует.\n";
        } else {
            echo "Ошибка при выполнении запроса " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }
}

// Заполним салоны тестовыми данными, если они пустые
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM salons");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO salons (name, address, phone, email, working_hours) VALUES 
            ('Главный автосалон Москва', 'ул. Тверская 15, Москва', '+7 (495) 123-45-67', 'msk@autosalon.ru', 'Пн-Вс: 09:00 - 21:00'),
            ('Филиал Санкт-Петербург', 'Невский пр-т 50, Санкт-Петербург', '+7 (812) 987-65-43', 'spb@autosalon.ru', 'Пн-Сб: 10:00 - 20:00')
        ");
        echo "Салоны заполнены тестовыми данными.\n";
    }
} catch (PDOException $e) {
    echo "Ошибка при вставке салонов: " . $e->getMessage() . "\n";
}

echo "Миграция завершена.\n";
?>
