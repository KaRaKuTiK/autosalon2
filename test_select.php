<?php
require 'config.php';
$stmt = $pdo->prepare('SELECT c.*, cr.id as cart_id FROM cart cr JOIN cars c ON cr.car_id = c.id WHERE cr.user_id = 3');
$stmt->execute();
echo "<pre>";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
?>
