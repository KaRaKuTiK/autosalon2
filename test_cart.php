<?php
session_start();
require_once 'config.php';
$user_id = $_SESSION['user']['id'] ?? 1;

$stmt = $pdo->prepare("
    SELECT c.id as car_id, cr.id as cart_id, c.brand, c.model 
    FROM cart cr 
    JOIN cars c ON cr.car_id = c.id 
");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($items);
echo "</pre>";

foreach ($items as $item) {
    echo '<a href="cart.php?delete=' . $item['cart_id'] . '">Delete ' . $item['brand'] . '</a><br>';
}
?>
