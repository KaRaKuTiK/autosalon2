<?php
require 'config.php';
$stmt = $pdo->query('SELECT * FROM cart');
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
