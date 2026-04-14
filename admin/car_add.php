<?php
require_once 'config.php';
$admin = checkAdminAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    // Валидация
    if (empty($brand) || empty($model) || $year < 1900 || $year > 2030 || $price <= 0 || empty($type)) {
        $error = 'Заполните все обязательные поля корректно';
    } else {
        try {
            // Добавляем автомобиль
            $stmt = $pdo->prepare("INSERT INTO cars (brand, model, year, price, color, type, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$brand, $model, $year, $price, $color, $type, $description]);
            $carId = $pdo->lastInsertId();
            
            // Обрабатываем фотографии, если они были загружены
            if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['photos']['name'][$key],
                            'type' => $_FILES['photos']['type'][$key],
                            'tmp_name' => $tmpName,
                            'size' => $_FILES['photos']['size'][$key]
                        ];
                        uploadImage($file, $carId);
                    }
                }
            }
            
            $_SESSION['success'] = 'Автомобиль успешно добавлен';
            header("Location: cars_list.php");
            exit();
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить автомобиль - Админ-панель</title>
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

        .form-container {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 800px;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-primary {
            background: var(--primary-orange);
            color: var(--text-light);
            flex: 1;
        }

        .btn-primary:hover {
            background: var(--hover-orange);
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 768px) {
            .form-grid {
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

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Добавить автомобиль</h1>

            <div class="form-container">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="brand">Марка <span class="required">*</span></label>
                            <input type="text" id="brand" name="brand" required value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="model">Модель <span class="required">*</span></label>
                            <input type="text" id="model" name="model" required value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="year">Год выпуска <span class="required">*</span></label>
                            <input type="number" id="year" name="year" min="1900" max="2030" required value="<?php echo htmlspecialchars($_POST['year'] ?? date('Y')); ?>">
                        </div>

                        <div class="form-group">
                            <label for="price">Цена (₽) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" min="0" step="0.01" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="color">Цвет</label>
                            <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($_POST['color'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="type">Тип <span class="required">*</span></label>
                            <select id="type" name="type" required>
                                <option value="">Выберите тип</option>
                                <option value="легковая" <?php echo (($_POST['type'] ?? '') === 'легковая') ? 'selected' : ''; ?>>Легковая</option>
                                <option value="внедорожник" <?php echo (($_POST['type'] ?? '') === 'внедорожник') ? 'selected' : ''; ?>>Внедорожник</option>
                                <option value="спорткар" <?php echo (($_POST['type'] ?? '') === 'спорткар') ? 'selected' : ''; ?>>Спорткар</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Описание</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="photos">К автомобилю можно сразу прикрепить фотографии (выберите несколько)</label>
                            <input type="file" id="photos" name="photos[]" multiple accept="image/jpeg, image/png, image/webp" style="background: rgba(255,255,255,0.05); padding: 10px;">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить автомобиль
                        </button>
                        <a href="cars_list.php" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>

                <div style="margin-top: 20px; padding: 15px; background: rgba(255, 102, 0, 0.1); border-radius: 10px; border: 1px solid rgba(255, 102, 0, 0.2);">
                    <i class="fas fa-info-circle" style="color: var(--primary-orange);"></i>
                    <strong>Примечание:</strong> Вы можете добавить фотографии сразу или сделать это позже на странице редактирования (включая выбор главной фото).
                </div>
            </div>
        </div>
    </div>
</body>
</html>
