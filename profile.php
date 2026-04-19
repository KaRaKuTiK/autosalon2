<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Обработка обновления данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';

    try {
        // Проверяем, не занят ли email другим пользователем
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Этот email уже используется другим пользователем";
        } else {
            // Обновляем данные пользователя
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, age = ?, gender = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $age, $gender, $user_id]);
            
            // Обновляем данные в сессии
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['success'] = "Данные успешно обновлены!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при обновлении данных: " . $e->getMessage();
    }
    
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - AutoSalon Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-black: #0a0a0a;
            --dark-black: #111111;
            --light-black: #1a1a1a;
            --primary-orange: #ff6600;
            --hover-orange: #ff8533;
            --light-orange: #ff944d;
            --text-light: #ffffff;
            --text-gray: #b0b0b0;
            --border-dark: #333333;
            --card-bg: rgba(26, 26, 26, 0.8);
            --success-green: #28a745;
            --warning-yellow: #ffc107;
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
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 102, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 102, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 102, 0, 0.07) 0%, transparent 50%);
            z-index: -1;
        }

        .header {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            border-bottom: 2px solid var(--primary-orange);
            box-shadow: 0 4px 20px rgba(255, 102, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .logo-icon {
            font-size: 2.5em;
            color: var(--primary-orange);
            filter: drop-shadow(0 0 10px rgba(255, 102, 0, 0.5));
        }

        .logo-text {
            color: var(--primary-orange);
            font-size: 2.5em;
            font-weight: 700;
            text-shadow: 0 0 20px rgba(255, 102, 0, 0.4);
        }

        .logo-text span {
            color: var(--text-light);
            font-weight: 300;
        }

        .nav-menu {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 102, 0, 0.2);
            position: sticky;
            top: 82px;
            z-index: 999;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 10px;
            list-style: none;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
        }

        .nav-links a:hover {
            background: rgba(255, 102, 0, 0.1);
            color: var(--primary-orange);
            border-color: rgba(255, 102, 0, 0.3);
        }

        .nav-links a.active {
            background: var(--primary-orange);
            color: var(--text-light);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .main-content {
            padding: 40px 0;
            position: relative;
        }

        .user-info {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-gray);
            font-size: 1.1em;
        }

        .user-info strong {
            color: var(--primary-orange);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 1.2em;
            font-weight: 300;
        }

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 968px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        .profile-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-orange), var(--hover-orange));
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .card-header i {
            font-size: 2em;
            color: var(--primary-orange);
        }

        .card-header h2 {
            color: var(--text-light);
            font-size: 1.8em;
            font-weight: 600;
        }

        .user-details {
            display: grid;
            gap: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-gray);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-label i {
            color: var(--primary-orange);
            width: 20px;
        }

        .detail-value {
            color: var(--text-light);
            font-weight: 600;
            text-align: right;
        }

        .edit-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            color: var(--text-light);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: var(--primary-orange);
            width: 16px;
        }

        .form-control {
            padding: 14px 16px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid var(--border-dark);
            border-radius: 10px;
            color: var(--text-light);
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
            background: rgba(50, 50, 50, 0.8);
        }

        .form-control:disabled {
            background: rgba(60, 60, 60, 0.5);
            color: var(--text-gray);
            cursor: not-allowed;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 1em;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-orange), var(--hover-orange));
            color: var(--text-light);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--hover-orange), var(--light-orange));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-orange);
            border: 2px solid var(--primary-orange);
        }

        .btn-secondary:hover {
            background: rgba(255, 102, 0, 0.1);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 2px solid rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.3);
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .form-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: rgba(255, 102, 0, 0.1);
            border: 1px solid rgba(255, 102, 0, 0.2);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 102, 0, 0.4);
        }

        .stat-icon {
            font-size: 2.5em;
            color: var(--primary-orange);
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 0.9em;
        }

        .footer {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px 0;
            border-top: 1px solid rgba(255, 102, 0, 0.3);
            margin-top: 60px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .copyright {
            color: var(--text-gray);
            font-size: 0.9em;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: var(--text-gray);
            font-size: 1.2em;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--primary-orange);
        }

        /* Сообщения об ошибках/успехе */
        .error-message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(220, 53, 69, 0.1));
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            border: 1px solid rgba(220, 53, 69, 0.3);
            backdrop-filter: blur(10px);
        }

        .success-message {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2), rgba(40, 167, 69, 0.1));
            color: #51cf66;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            border: 1px solid rgba(40, 167, 69, 0.3);
            backdrop-filter: blur(10px);
        }

        /* Анимации */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .detail-value {
                text-align: left;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .nav-links {
                gap: 5px;
            }
            
            .nav-links a {
                padding: 10px 15px;
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .profile-card {
                padding: 25px 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 2.2em;
            }
        }

        /* Переключатель режима редактирования */
        .edit-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(255, 102, 0, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 102, 0, 0.2);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--border-dark);
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: var(--text-light);
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary-orange);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .toggle-label {
            color: var(--text-light);
            font-weight: 500;
        }

        /* Декоративные элементы */
        .floating-car {
            position: absolute;
            font-size: 2em;
            opacity: 0.05;
            z-index: -1;
            animation: float 8s ease-in-out infinite;
        }

        .car-1 {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .car-2 {
            bottom: 20%;
            right: 8%;
            animation-delay: 2s;
        }

        .car-3 {
            top: 40%;
            right: 15%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
    </style>
</head>
<body>
    <!-- Декоративные плавающие иконки машин -->
    <div class="floating-car car-1">🚗</div>
    <div class="floating-car car-2">🏎️</div>
    <div class="floating-car car-3">🚙</div>

    <div class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-car-side logo-icon"></i>
                <div class="logo-text">AUTO<span>SALON</span></div>
            </div>
        </div>
    </div>

    <div class="nav-menu">
        <div class="container">
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-car"></i> Каталог</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Личный кабинет</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Корзина</a></li>
                <li><a href="support.php"><i class="fas fa-headset"></i> Поддержка</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> О сайте</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Добро пожаловать в ваш личный кабинет, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>!
            </div>

            <div class="page-header">
                <h1 class="page-title">Личный кабинет</h1>
                <p class="page-subtitle">Управляйте вашими данными и настройками</p>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="success-message"><i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <div class="profile-container">
                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-id-card"></i>
                        <h2>Основная информация</h2>
                    </div>
                    <div class="user-details">
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-user"></i>
                                ФИО:
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-at"></i>
                                Логин:
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['login']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-envelope"></i>
                                Email:
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-birthday-cake"></i>
                                Возраст:
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['age']); ?> лет</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-venus-mars"></i>
                                Пол:
                            </span>
                            <span class="detail-value"><?php echo $user['gender'] == 'male' ? 'Мужской' : 'Женский'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="fas fa-calendar-alt"></i>
                                Дата регистрации:
                            </span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['registration_date']); ?></span>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">В корзине</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">В избранном</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stat-value">0</div>
                            <div class="stat-label">Просмотрено</div>
                        </div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i>
                        <h2>Редактирование данных</h2>
                    </div>
                    
                    <form action="profile.php" method="POST" class="edit-form">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="form-group">
                            <label for="full_name">
                                <i class="fas fa-user"></i>
                                Полное имя
                            </label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email адрес
                            </label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">
                                <i class="fas fa-birthday-cake"></i>
                                Возраст
                            </label>
                            <input type="number" id="age" name="age" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['age']); ?>" min="18" max="120" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">
                                <i class="fas fa-venus-mars"></i>
                                Пол
                            </label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                                <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Сохранить изменения
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                                Сбросить
                            </button>
                        </div>
                    </form>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--text-light); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-shield-alt"></i>
                            Безопасность
                        </h3>
                        <a href="#" class="btn btn-secondary" style="width: 100%; justify-content: center;" onclick="alert('Функция смены пароля в разработке')">
                            <i class="fas fa-key"></i>
                            Сменить пароль
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="copyright">
                    <p>&copy; 2024 AutoSalon Premium. Все права защищены.</p>
                    <p>Учебный проект по веб-разработке</p>
                </div>
                <div class="social-links">
                    <a href="#"><i class="fab fa-vk"></i></a>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Подтверждение сброса формы
        document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите сбросить все изменения?')) {
                e.preventDefault();
            }
        });

        // Валидация формы
        document.querySelector('form').addEventListener('submit', function(e) {
            const age = document.getElementById('age').value;
            if (age < 18) {
                e.preventDefault();
                alert('Для регистрации вам должно быть не менее 18 лет');
                return false;
            }
            return true;
        });

        // Плавное появление элементов
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.profile-card, .stats-grid');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>

    <?php include 'chat_widget.php'; ?>
</body>
</html>