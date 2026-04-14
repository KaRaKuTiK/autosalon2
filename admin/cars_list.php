<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Обработка удаления автомобиля
if (isset($_GET['delete'])) {
    $carId = (int)$_GET['delete'];
    
    try {
        // Удаляем все фотографии автомобиля
        $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ?");
        $stmt->execute([$carId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($images as $image) {
            $filePath = '../' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Удаляем автомобиль (каскадно удалятся записи из car_images)
        $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->execute([$carId]);
        
        $_SESSION['success'] = 'Автомобиль успешно удалён';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка при удалении: ' . $e->getMessage();
    }
    
    header("Location: cars_list.php");
    exit();
}

// Получаем список всех автомобилей
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM car_images WHERE car_id = c.id) as images_count,
               (SELECT image_path FROM car_images WHERE car_id = c.id AND is_main = 1 LIMIT 1) as main_image
        FROM cars c 
        ORDER BY c.id DESC
    ");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cars = [];
    $_SESSION['error'] = 'Ошибка при загрузке автомобилей';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление автомобилями - Админ-панель</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-black) 50%, #1a0f00 100%);
            color: var(--text-light);
            min-height: 100vh;
        }

        .header {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            border-bottom: 2px solid var(--primary-orange);
            box-shadow: 0 4px 20px rgba(255, 102, 0, 0.2);
        }

        .container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            font-size: 2em;
            color: var(--primary-orange);
        }

        .logo-text {
            color: var(--primary-orange);
            font-size: 1.8em;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-orange);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background: var(--hover-orange);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .main-content {
            padding: 40px 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2.5em;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .success-message, .error-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .error-message {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .cars-table {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(255, 102, 0, 0.1);
        }

        th {
            padding: 20px;
            text-align: left;
            color: var(--primary-orange);
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 102, 0, 0.2);
        }

        td {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(255, 102, 0, 0.05);
        }

        .car-image-preview {
            width: 80px;
            height: 60px;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
        }

        .car-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .car-type-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255, 102, 0, 0.1);
            color: var(--primary-orange);
            border-radius: 15px;
            font-size: 0.85em;
            border: 1px solid rgba(255, 102, 0, 0.3);
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 0.9em;
        }

        .btn-edit {
            background: rgba(0, 123, 255, 0.2);
            color: #4dabf7;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }

        .btn-edit:hover {
            background: rgba(0, 123, 255, 0.3);
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .btn-delete:hover {
            background: rgba(220, 53, 69, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 4em;
            color: var(--primary-orange);
            opacity: 0.5;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .cars-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
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
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад
                    </a>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Управление автомобилями</h1>
                <a href="car_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить автомобиль
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="cars-table">
                <?php if (empty($cars)): ?>
                    <div class="empty-state">
                        <i class="fas fa-car"></i>
                        <h3>Автомобили не найдены</h3>
                        <p>Добавьте первый автомобиль в каталог</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Фото</th>
                                <th>Марка и модель</th>
                                <th>Год</th>
                                <th>Цена</th>
                                <th>Тип</th>
                                <th>Фотографий</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <td><?php echo $car['id']; ?></td>
                                    <td>
                                        <div class="car-image-preview">
                                            <?php if ($car['main_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($car['main_image']); ?>" alt="<?php echo htmlspecialchars($car['brand']); ?>">
                                            <?php else: ?>
                                                🚗
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($car['year']); ?></td>
                                    <td><?php echo number_format($car['price'], 0, ',', ' '); ?> ₽</td>
                                    <td><span class="car-type-badge"><?php echo htmlspecialchars($car['type']); ?></span></td>
                                    <td><?php echo $car['images_count']; ?> шт.</td>
                                    <td>
                                        <div class="actions">
                                            <a href="car_edit.php?id=<?php echo $car['id']; ?>" class="btn btn-edit btn-small">
                                                <i class="fas fa-edit"></i> Редактировать
                                            </a>
                                            <a href="?delete=<?php echo $car['id']; ?>" 
                                               class="btn btn-delete btn-small" 
                                               onclick="return confirm('Вы уверены, что хотите удалить этот автомобиль? Все фотографии также будут удалены.')">
                                                <i class="fas fa-trash"></i> Удалить
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
