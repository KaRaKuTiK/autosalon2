<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Автоматически добавляем недостающие связи машина-салон в таблицу inventory
try {
    $pdo->exec("
        INSERT IGNORE INTO inventory (car_id, salon_id, quantity, price)
        SELECT c.id, s.id, 0, c.price 
        FROM cars c CROSS JOIN salons s
    ");
} catch (PDOException $e) {
    // Игнорируем ошибку при дубликатах
}

// Обработка сохранения изменений инвентаря
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_inventory'])) {
    $inv_ids = $_POST['inv_id'] ?? [];
    $quantities = $_POST['qty'] ?? [];
    $prices = $_POST['price'] ?? [];
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE inventory SET quantity = ?, price = ? WHERE id = ?");
        
        for ($i = 0; $i < count($inv_ids); $i++) {
            $id = (int)$inv_ids[$i];
            $qty = (int)$quantities[$i];
            $price = (float)$prices[$i];
            $stmt->execute([$qty, $price, $id]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Данные инвентаря успешно обновлены.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Ошибка при сохранении: " . $e->getMessage();
    }
    
    header("Location: inventory.php");
    exit();
}

// Загружаем данные инвентаря
$stmt = $pdo->query("
    SELECT i.id as inv_id, i.quantity, i.price as inv_price,
           c.brand, c.model, c.year,
           s.name as salon_name
    FROM inventory i
    JOIN cars c ON i.car_id = c.id
    JOIN salons s ON i.salon_id = s.id
    ORDER BY c.brand, c.model, s.name
");
$inventoryList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление инвентарем - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-black: #0a0a0a;
            --dark-black: #111111;
            --primary-orange: #ff6600;
            --hover-orange: #ff8533;
            --text-light: #ffffff;
            --text-gray: #b0b0b0;
            --card-bg: rgba(26, 26, 26, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-black) 50%, #1a0f00 100%); color: var(--text-light); min-height: 100vh; }
        .header { background: rgba(10, 10, 10, 0.95); backdrop-filter: blur(10px); padding: 20px 0; border-bottom: 2px solid var(--primary-orange); box-shadow: 0 4px 20px rgba(255, 102, 0, 0.2); }
        .container { width: 90%; max-width: 1400px; margin: 0 auto; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-icon { font-size: 2em; color: var(--primary-orange); }
        .logo-text { color: var(--primary-orange); font-size: 1.8em; font-weight: 700; }
        
        .main-content { padding: 40px 0; }
        .page-title { font-size: 2.5em; margin-bottom: 30px; text-align: center; color: var(--primary-orange); }

        .table-container { background: rgba(26, 26, 26, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 10px; padding: 20px; overflow-x: auto; }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .table-title { display: flex; align-items: center; gap: 10px; color: var(--primary-orange); font-size: 1.2em; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; }
        th { background: rgba(255, 102, 0, 0.1); color: var(--primary-orange); padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .form-control { 
            width: 120px; 
            padding: 8px; 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 4px; 
            color: #fff; 
        }
        .form-control:focus { outline: none; border-color: var(--primary-orange); }

        .btn { padding: 10px 25px; background: var(--primary-orange); color: #fff; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 1.1em;}
        .btn:hover { background: var(--hover-orange); }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(40, 167, 69, 0.2); color: #51cf66; border: 1px solid rgba(40, 167, 69, 0.3); }
        .alert-error { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid rgba(220, 53, 69, 0.3); }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-shield-alt logo-icon"></i>
                    <div class="logo-text">ADMIN PANEL</div>
                </div>
                <div style="color: var(--text-gray);">
                    <i class="fas fa-user-shield"></i> Администратор: <strong style="color: #fff;"><?php echo htmlspecialchars($admin['login'] ?? 'admin'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Инвентарь автомобилей</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <form method="POST" action="inventory.php">
                    <div class="table-header">
                        <div class="table-title">
                            <i class="fas fa-boxes"></i> Наличие по салонам
                        </div>
                        <button type="submit" name="save_inventory" class="btn"><i class="fas fa-save"></i> Сохранить изменения</button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Автомобиль (Марка Модель Год)</th>
                                <th>Салон</th>
                                <th>Количество (шт)</th>
                                <th>Цена (₽)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inventoryList)): ?>
                            <tr><td colspan="4" style="text-align: center;">Ассортимент пуст. Добавьте автомобили и салоны.</td></tr>
                            <?php else: ?>
                                <?php foreach ($inventoryList as $row): ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="inv_id[]" value="<?php echo $row['inv_id']; ?>">
                                        <strong><?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?></strong> 
                                        <span style="color: var(--text-gray);">(<?php echo $row['year']; ?>)</span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['salon_name']); ?></td>
                                    <td>
                                        <input type="number" name="qty[]" class="form-control" value="<?php echo $row['quantity']; ?>" min="0">
                                    </td>
                                    <td>
                                        <input type="number" name="price[]" class="form-control" value="<?php echo $row['inv_price']; ?>" step="0.01" min="0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
