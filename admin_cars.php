<?php
session_start();
require_once 'config.php';

// Проверка доступа к админ-панели (роль админа и сессия)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Создаем папку для загрузок, если её нет
$upload_dir = __DIR__ . '/uploads/cars/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Обработка действий (загрузка, удаление, выбор главной)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload' && isset($_FILES['images']) && isset($_POST['car_id'])) {
        $car_id = (int)$_POST['car_id'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'heic'];
        
        $files = $_FILES['images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === 0) {
                $file_tmp = $files['tmp_name'][$i];
                $file_name = $files['name'][$i];
                $file_size = $files['size'][$i];
                
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                if (in_array($mime, $allowed_types) || in_array($file_ext, $allowed_ext)) {
                    $new_filename = uniqid('car_' . $car_id . '_') . '.' . $file_ext;
                    $dest_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        // Сохраняем в БД (по умолчанию не главная)
                        $stmt = $pdo->prepare("INSERT INTO car_images (car_id, image_path) VALUES (?, ?)");
                        $stmt->execute([$car_id, 'uploads/cars/' . $new_filename]);
                    }
                }
            }
        }
        $_SESSION['success'] = "Фотографии успешно загружены!";
        header("Location: admin_cars.php?car_id=" . $car_id);
        exit();
    }

    if ($action === 'delete' && isset($_POST['image_id']) && isset($_POST['car_id'])) {
        $image_id = (int)$_POST['image_id'];
        $car_id = (int)$_POST['car_id'];

        $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE id = ?");
        $stmt->execute([$image_id]);
        $image = $stmt->fetch();

        if ($image) {
            $file_path = __DIR__ . '/' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $stmt = $pdo->prepare("DELETE FROM car_images WHERE id = ?");
            $stmt->execute([$image_id]);
        }
        $_SESSION['success'] = "Фото удалено!";
        header("Location: admin_cars.php?car_id=" . $car_id);
        exit();
    }

    if ($action === 'set_main' && isset($_POST['image_id']) && isset($_POST['car_id'])) {
        $image_id = (int)$_POST['image_id'];
        $car_id = (int)$_POST['car_id'];

        // Сбрасываем все фото машины
        $stmt = $pdo->prepare("UPDATE car_images SET is_main = 0 WHERE car_id = ?");
        $stmt->execute([$car_id]);

        // Устанавливаем главную
        $stmt = $pdo->prepare("UPDATE car_images SET is_main = 1 WHERE id = ?");
        $stmt->execute([$image_id]);

        $_SESSION['success'] = "Главное фото обновлено!";
        header("Location: admin_cars.php?car_id=" . $car_id);
        exit();
    }
}

// Получение списка всех автомобилей
$stmt = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Выбранный автомобиль для редактирования фото
$selected_car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$selected_car = null;
$car_images = [];

if ($selected_car_id) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$selected_car_id]);
    $selected_car = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_car) {
        $stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_main DESC, id DESC");
        $stmt->execute([$selected_car_id]);
        $car_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель: Управление автомобилями</title>
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
            --border-dark: #333333;
            --card-bg: rgba(26, 26, 26, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }

        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-black) 50%, #1a0f00 100%);
            color: var(--text-light);
            min-height: 100vh;
        }

        .header {
            background: rgba(10, 10, 10, 0.95);
            padding: 20px 0;
            border-bottom: 2px solid var(--primary-orange);
            text-align: center;
        }

        .container {
            width: 95%; max-width: 1400px; margin: 30px auto;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .cars-list {
            background: var(--card-bg);
            border: 1px solid var(--border-dark);
            border-radius: 15px; padding: 20px;
            max-height: 80vh; overflow-y: auto;
        }

        .car-item {
            display: block; text-decoration: none;
            padding: 15px; border-bottom: 1px solid var(--border-dark);
            color: var(--text-light); transition: all 0.3s;
        }
        
        .car-item:hover, .car-item.active {
            background: rgba(255, 102, 0, 0.1); border-left: 4px solid var(--primary-orange);
        }

        .photo-manager {
            background: var(--card-bg);
            border: 1px solid rgba(255,102,0, 0.2);
            border-radius: 15px; padding: 30px;
        }

        .btn {
            background: var(--primary-orange); color: var(--text-light);
            border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover { background: var(--hover-orange); }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }

        .upload-form { margin-bottom: 30px; padding: 20px; background: rgba(0,0,0,0.5); border-radius: 10px; border: 1px dashed var(--primary-orange); }

        .gallery {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;
        }

        .img-card {
            background: #222; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-dark);
            position: relative;
        }
        
        .img-card img { width: 100%; height: 150px; object-fit: cover; display: block; }
        
        .img-actions { padding: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        .badge-main { position: absolute; top: 10px; left: 10px; background: var(--primary-orange); color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 0.8em; font-weight: bold; }

        @media (max-width: 900px) {
            .admin-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="header">
    <h2 style="color: var(--primary-orange);">АДМИН-ПАНЕЛЬ: ФОТОГРАФИИ АВТО</h2>
    <a href="dashboard.php" style="color: #ccc; text-decoration: none; margin-top: 10px; display: inline-block;">&larr; Вернуться в каталог</a>
</div>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="admin-layout">
        <!-- Левая колонка: Список машин -->
        <div class="cars-list">
            <h3><i class="fas fa-car"></i> Список автомобилей</h3>
            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #333;">
            <?php foreach ($cars as $car): ?>
                <a href="?car_id=<?php echo $car['id']; ?>" class="car-item <?php echo $selected_car_id == $car['id'] ? 'active' : ''; ?>">
                    <strong><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></strong><br>
                    <small style="color: var(--text-gray);"><?php echo $car['year']; ?> / <?php echo number_format($car['price'], 0, ',', ' '); ?> ₽</small>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Правая колонка: Загрузка фото -->
        <div class="photo-manager">
            <?php if ($selected_car): ?>
                <h3>Управление фото: <span style="color: var(--primary-orange);"><?php echo htmlspecialchars($selected_car['brand'] . ' ' . $selected_car['model']); ?></span></h3>
                <p style="color: var(--text-gray); margin-bottom: 20px;">Поддерживаемые форматы: JPG, PNG, WEBP, HEIC</p>
                
                <form action="admin_cars.php" method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="car_id" value="<?php echo $selected_car['id']; ?>">
                    <div style="margin-bottom: 15px;">
                        <input type="file" name="images[]" multiple required accept=".jpg,.jpeg,.png,.webp,.heic,image/jpeg,image/png,image/webp,image/heic" style="color: #fff; width: 100%;">
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-upload"></i> Загрузить фотографии</button>
                </form>

                <h4>Загруженные фотографии (<?php echo count($car_images); ?>)</h4>
                <hr style="margin: 15px 0; border: 0; border-top: 1px solid #333;">
                
                <?php if (count($car_images) > 0): ?>
                    <div class="gallery">
                        <?php foreach ($car_images as $img): ?>
                            <div class="img-card">
                                <?php if ($img['is_main']): ?>
                                    <div class="badge-main"><i class="fas fa-star"></i> Главное</div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Car Photo">
                                <div class="img-actions">
                                    <form action="admin_cars.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="set_main">
                                        <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                        <input type="hidden" name="car_id" value="<?php echo $selected_car['id']; ?>">
                                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8em;" <?php echo $img['is_main'] ? 'disabled' : ''; ?> title="Сделать главной">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="admin_cars.php" method="POST" style="display:inline;" onsubmit="return confirm('Удалить это фото?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                        <input type="hidden" name="car_id" value="<?php echo $selected_car['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;" title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-gray);">Нет загруженных фотографий для этого автомобиля.</p>
                <?php endif; ?>
                
            <?php else: ?>
                <div style="text-align: center; padding: 50px 0; color: var(--text-gray);">
                    <i class="fas fa-car" style="font-size: 4em; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>Выберите автомобиль из списка слева для управления фотографиями</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
