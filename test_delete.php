<?php
session_start();
require_once 'config.php';
$user_id = $_SESSION['user']['id'] ?? 1;

if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$delete_id, $user_id]);
        echo "Deleted cart_id $delete_id for user_id $user_id. Rows affected: " . $stmt->rowCount() . "<br>";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($items);
echo "</pre>";

foreach ($items as $item) {
    echo '<a href="test_delete.php?delete=' . $item['id'] . '">Delete ID ' . $item['id'] . '</a><br>';
}
?>
