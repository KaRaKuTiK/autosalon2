<?php
require_once 'config.php';

$queries = [
    "ALTER TABLE users ADD COLUMN last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    "ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) DEFAULT 0",
    "ALTER TABLE users ADD COLUMN is_deleted TINYINT(1) DEFAULT 0",
    "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE test_drive_requests ADD COLUMN user_id INT NULL"
];

foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "Successfully executed: $query<br>";
    } catch (PDOException $e) {
        // Ignored if column already exists (Error 1060)
        if ($e->getCode() == '42S21') {
            echo "Already exists (Skipped): $query<br>";
        } else {
            echo "Error executing '$query': " . $e->getMessage() . "<br>";
        }
    }
}
echo "Database update completed.";
?>
