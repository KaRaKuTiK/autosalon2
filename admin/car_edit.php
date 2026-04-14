<?php
require_once 'config.php';
$admin = checkAdminAuth();

$carId = (int)($_GET['id'] ?? 0);

if ($carId <= 0) {
    header("Location: cars_list.php");
    exit();
}

// Получаем данные автомобиля
try {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$carId]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car) {
        $_SESSION['error'] = 'Автомобиль не найден';
        header("Location: cars_list.php");
        exit();
    }
    
    // Получаем фотографии
    $images = getCarImages($carId);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Ошибка при загрузке данных';
    header("Location: cars_list.php");
    exit();
}

$error = '';
$success = '';

// Обработка обновления данных автомобиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_car'])) {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    if (empty($brand) || empty($model) || $year < 1900 || $year > 2030 || $price <= 0 || empty($type)) {
        $error = 'Заполните все обязательные поля корректно';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE cars SET brand = ?, model = ?, year = ?, price = ?, color = ?, type = ?, description = ? WHERE id = ?");
            $stmt->execute([$brand, $model, $year, $price, $color, $type, $description, $carId]);
            
            $success = 'Данные автомобиля успешно обновлены';
            $car = array_merge($car, compact('brand', 'model', 'year', 'price', 'color', 'type', 'description'));
        } catch (PDOException $e) {
            $error = 'Ошибка при обновлении: ' . $e->getMessage();
        }
    }
}

// Обработка загрузки фотографий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    $uploadedCount = 0;
    $errors = [];
    
    foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $_FILES['photos']['name'][$key],
                'type' => $_FILES['photos']['type'][$key],
                'tmp_name' => $tmpName,
                'size' => $_FILES['photos']['size'][$key]
            ];
            
            $result = uploadImage($file, $carId);
            
            if ($result['success']) {
                $uploadedCount++;
            } else {
                $errors[] = $result['error'];
            }
        }
    }
    
    if ($uploadedCount > 0) {
        $success = "Загружено фотографий: $uploadedCount";
        $images = getCarImages($carId); // Обновляем список
    }
    
    if (!empty($errors)) {
        $error = implode('; ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать автомобиль - Админ-панель</title>
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

        .page-title {
            font-size: 2.5em;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-container, .photos-container {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .section-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: var(--primary-orange);
        }

        .success-message, .error-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
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

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-gray);
            font-weight: 500;
        }

        label .required {
            color: #ff6b6b;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: var(--text-light);
            font-size: 1em;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-primary {
            background: var(--primary-orange);
            color: var(--text-light);
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--hover-orange);
            transform: translateY(-2px);
        }

        .upload-area {
            border: 2px dashed rgba(255, 102, 0, 0.3);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: var(--primary-orange);
            background: rgba(255, 102, 0, 0.05);
        }

        .upload-area i {
            font-size: 3em;
            color: var(--primary-orange);
            margin-bottom: 15px;
        }

        .upload-info {
            color: var(--text-gray);
            font-size: 0.9em;
            margin-top: 10px;
        }

        #photoInput {
            display: none;
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .photo-item {
            position: relative;
            aspect-ratio: 4/3;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-item.main {
            border-color: var(--primary-orange);
        }

        .photo-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: var(--primary-orange);
            color: var(--text-light);
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 0.75em;
            font-weight: 600;
        }

        .photo-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            padding: 8px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .photo-item:hover .photo-actions {
            opacity: 1;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.85em;
            flex: 1;
        }

        .btn-set-main {
            background: rgba(0, 123, 255, 0.8);
            color: var(--text-light);
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.8);
            color: var(--text-light);
        }

        .empty-photos {
            text-align: center;
            padding: 40px;
            color: var(--text-gray);
        }

        .empty-photos i {
            font-size: 3em;
            color: var(--primary-orange);
            opacity: 0.5;
            margin-bottom: 15px;
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
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
                <a href="cars_list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Редактировать: <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="content-grid">
                <!-- Форма редактирования данных -->
                <div class="form-container">
                    <h2 class="section-title"><i class="fas fa-edit"></i> Основные данные</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="update_car" value="1">
                        
                        <div class="form-group">
                            <label for="brand">Марка <span class="required">*</span></label>
                            <input type="text" id="brand" name="brand" required value="<?php echo htmlspecialchars($car['brand']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="model">Модель <span class="required">*</span></label>
                            <input type="text" id="model" name="model" required value="<?php echo htmlspecialchars($car['model']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="year">Год выпуска <span class="required">*</span></label>
                            <input type="number" id="year" name="year" min="1900" max="2030" required value="<?php echo htmlspecialchars($car['year']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="price">Цена (₽) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" min="0" step="0.01" required value="<?php echo htmlspecialchars($car['price']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="color">Цвет</label>
                            <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($car['color']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="type">Тип <span class="required">*</span></label>
                            <select id="type" name="type" required>
                                <option value="легковая" <?php echo ($car['type'] === 'легковая') ? 'selected' : ''; ?>>Легковая</option>
                                <option value="внедорожник" <?php echo ($car['type'] === 'внедорожник') ? 'selected' : ''; ?>>Внедорожник</option>
                                <option value="спорткар" <?php echo ($car['type'] === 'спорткар') ? 'selected' : ''; ?>>Спорткар</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Описание</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($car['description']); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                    </form>
                </div>

                <!-- Управление фотографиями -->
                <div class="photos-container">
                    <h2 class="section-title"><i class="fas fa-images"></i> Фотографии</h2>
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                        <div class="upload-area" onclick="document.getElementById('photoInput').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div><strong>Нажмите для загрузки фотографий</strong></div>
                            <div class="upload-info">Поддерживаются: JPG, PNG, WEBP (макс. 5MB)</div>
                        </div>
                        <input type="file" id="photoInput" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
                    </form>

                    <div class="photos-grid">
                        <?php if (empty($images)): ?>
                            <div class="empty-photos" style="grid-column: 1/-1;">
                                <i class="fas fa-image"></i>
                                <p>Фотографии не загружены</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($images as $image): ?>
                                <div class="photo-item <?php echo $image['is_main'] ? 'main' : ''; ?>">
                                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Фото">
                                    <?php if ($image['is_main']): ?>
                                        <div class="photo-badge">Главное</div>
                                    <?php endif; ?>
                                    <div class="photo-actions">
                                        <?php if (!$image['is_main']): ?>
                                            <a href="set_main_image.php?id=<?php echo $image['id']; ?>&car=<?php echo $carId; ?>" 
                                               class="btn btn-set-main btn-small"
                                               title="Сделать главным">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="delete_image.php?id=<?php echo $image['id']; ?>&car=<?php echo $carId; ?>" 
                                           class="btn btn-delete btn-small"
                                           onclick="return confirm('Удалить это фото?')"
                                           title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
