<?php
require_once 'config.php';
$stmt = $pdo->query('SHOW CREATE TABLE cart');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($result);
echo "</pre>";
?>
