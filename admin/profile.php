<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Если требуют ввести ФИО
$requireFio = isset($_SESSION['require_profile']);

$upload_dir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    
    if (empty($fullName)) {
        $_SESSION['error'] = "ФИО (Полное имя) не может быть пустым.";
    } else {
        $avatarPath = $admin['avatar'] ?? null;
        
        // Обработка загрузки фото
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fileInfo, $_FILES['avatar']['tmp_name']);
            finfo_close($fileInfo);
            
            if (in_array($mime, $allowed_types) && $_FILES['avatar']['size'] <= 2 * 1024 * 1024) {
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $newName = 'admin_' . $admin['id'] . '_' . time() . '.' . $ext;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $newName)) {
                    // Удаляем старый аватар
                    if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
                        @unlink(__DIR__ . '/../' . $avatarPath);
                    }
                    $avatarPath = 'uploads/avatars/' . $newName;
                }
            } else {
                $_SESSION['error'] = "Недопустимый формат файла (разрешены JPG, PNG, WEBP) или превышен размер (до 2 МБ).";
            }
        }
        
        if (!isset($_SESSION['error'])) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$fullName, $avatarPath, $admin['id']]);
            
            // Обновляем сессию
            $_SESSION['admin']['full_name'] = $fullName;
            $_SESSION['admin']['avatar'] = $avatarPath;
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['avatar'] = $avatarPath;
            $admin = $_SESSION['admin'];
            
            if ($requireFio) {
                unset($_SESSION['require_profile']);
                $_SESSION['success'] = "Добро пожаловать! Ваш профиль успешно настроен.";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['success'] = "Данные профиля успешно обновлены.";
                header("Location: profile.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль - Админ-панель</title>
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
        
        .header { background: rgba(10, 10, 10, 0.95); backdrop-filter: blur(10px); padding: 20px 0; border-bottom: 2px solid var(--primary-orange); }
        .container { width: 90%; max-width: 800px; margin: 0 auto; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { display:flex; align-items:center; gap:15px; }
        .logo-icon { font-size: 2em; color: var(--primary-orange); }
        .logo-text { color: var(--primary-orange); font-size: 1.8em; font-weight: 700; }
        
        .main-content { padding: 40px 0; }
        .profile-card {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
        }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: rgba(40, 167, 69, 0.2); color: #51cf66; border: 1px solid rgba(40, 167, 69, 0.3); }
        .alert-danger { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid rgba(220, 53, 69, 0.3); }
        .alert-info { background: rgba(23, 162, 184, 0.2); color: #48C9B0; border: 1px solid rgba(23, 162, 184, 0.3); }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; color: var(--text-gray); }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            border-radius: 10px;
            color: #fff;
            transition: all 0.3s;
        }
        .form-control:focus { border-color: var(--primary-orange); outline: none; }

        .avatar-preview-container { text-align: center; margin-bottom: 30px; }
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-orange);
            margin: 0 auto 15px;
            display: block;
            background: #222;
        }
        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px dashed var(--primary-orange);
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            color: var(--primary-orange);
            background: rgba(255,102,0,0.1);
        }

        .btn {
            background: linear-gradient(135deg, var(--primary-orange), var(--hover-orange));
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            width: 100%;
            transition: transform 0.3s;
        }
        .btn:hover { transform: translateY(-2px); }

        .custom-file-upload {
            display: inline-block;
            padding: 8px 15px;
            cursor: pointer;
            background: rgba(255,255,255,0.1);
            color: #ccc;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background 0.3s;
        }
        .custom-file-upload:hover { background: rgba(255,255,255,0.2); color: #fff; }
        input[type="file"] { display: none; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container" style="max-width:1400px; width:90%;">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-shield-alt logo-icon"></i>
                    <div class="logo-text">ADMIN PANEL</div>
                </div>
                <!-- Не выводим кнопку выхода при принудительном вводе ФИО -->
            </div>
        </div>
    </div>

    <?php if (!$requireFio) include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="profile-card">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="color:var(--text-light);"><?php echo $requireFio ? 'Укажите свои данные' : 'Профиль администратора'; ?></h2>
                    <p style="color:var(--text-gray); margin-top: 10px;">Для обращений клиентов в онлайн-чате используется это имя и фото.</p>
                </div>

                <?php if ($requireFio): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Пожалуйста, заполните ваше Полное имя для продолжения работы.
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="avatar-preview-container">
                        <?php if (!empty($admin['avatar'])): ?>
                            <img src="../<?php echo htmlspecialchars($admin['avatar']); ?>" alt="Аватар" class="avatar-preview" id="previewImg">
                        <?php else: ?>
                            <div class="avatar-placeholder" id="previewPlaceholder"><i class="fas fa-user-tie"></i></div>
                            <img src="" alt="Аватар" class="avatar-preview" id="previewImg" style="display:none;">
                        <?php endif; ?>
                        
                        <label class="custom-file-upload">
                            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" onchange="previewFile()">
                            <i class="fas fa-upload"></i> Выбрать новую фотографию
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Полное имя (ФИО) *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Иванов Иван" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email (для информации)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled>
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> <?php echo $requireFio ? 'Сохранить и войти' : 'Сохранить изменения'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function previewFile() {
            const preview = document.getElementById('previewImg');
            const placeholder = document.getElementById('previewPlaceholder');
            const file = document.querySelector('input[type=file]').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
                preview.style.display = 'block';
                if(placeholder) placeholder.style.display = 'none';
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
