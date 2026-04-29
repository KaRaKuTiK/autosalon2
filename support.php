<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Обработка отправки формы обратной связи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_feedback'])) {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($subject) || empty($message)) {
        $_SESSION['error'] = "Пожалуйста, заполните все поля формы";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, subject, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $subject, $message]);
            
            $_SESSION['success'] = "Ваше сообщение успешно отправлено! Мы ответим вам в ближайшее время.";
            
            // Очищаем поля формы
            $subject = '';
            $message = '';
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при отправке сообщения: " . $e->getMessage();
        }
    }
}

// Получаем историю сообщений пользователя
try {
    $stmt = $pdo->prepare("
        SELECT subject, message, status, created_at 
        FROM feedback 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $feedback_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $feedback_history = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поддержка - AutoSalon Premium</title>
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
            --info-blue: #17a2b8;
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
            margin-bottom: 50px;
        }

        .page-title {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 1.2em;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .support-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 968px) {
            .support-container {
                grid-template-columns: 1fr;
            }
        }

        .support-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .support-card::before {
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
            font-size: 2.5em;
            color: var(--primary-orange);
        }

        .card-header h2 {
            color: var(--text-light);
            font-size: 1.8em;
            font-weight: 600;
        }

        .contact-methods {
            display: grid;
            gap: 20px;
        }

        .contact-method {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: rgba(40, 40, 40, 0.5);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .contact-method:hover {
            border-color: rgba(255, 102, 0, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-orange), var(--hover-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: var(--text-light);
            flex-shrink: 0;
        }

        .contact-info {
            flex: 1;
        }

        .contact-title {
            color: var(--text-light);
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .contact-detail {
            color: var(--text-gray);
            font-size: 1.1em;
            margin-bottom: 8px;
        }

        .contact-note {
            color: var(--primary-orange);
            font-size: 0.9em;
            font-weight: 500;
        }

        .feedback-form {
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
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
            background: rgba(50, 50, 50, 0.8);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
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

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .form-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .feedback-history {
            margin-top: 30px;
        }

        .history-title {
            color: var(--text-light);
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-title i {
            color: var(--primary-orange);
        }

        .history-list {
            display: grid;
            gap: 15px;
        }

        .history-item {
            background: rgba(40, 40, 40, 0.5);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .history-item:hover {
            border-color: rgba(255, 102, 0, 0.2);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .history-subject {
            color: var(--text-light);
            font-weight: 600;
            font-size: 1.1em;
        }

        .history-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .status-new {
            background: var(--warning-yellow);
            color: #000;
        }

        .status-in_progress {
            background: var(--info-blue);
            color: var(--text-light);
        }

        .status-resolved {
            background: var(--success-green);
            color: var(--text-light);
        }

        .history-message {
            color: var(--text-gray);
            line-height: 1.5;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .history-date {
            color: var(--text-gray);
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .history-date i {
            color: var(--primary-orange);
            font-size: 0.8em;
        }

        .empty-history {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-gray);
        }

        .empty-history i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .working-hours {
            background: rgba(23, 162, 184, 0.1);
            border: 1px solid rgba(23, 162, 184, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }

        .hours-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .hours-header i {
            font-size: 1.5em;
            color: var(--info-blue);
        }

        .hours-header h3 {
            color: var(--text-light);
            font-size: 1.3em;
            font-weight: 600;
        }

        .hours-grid {
            display: grid;
            gap: 12px;
        }

        .hour-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .hour-row:last-child {
            border-bottom: none;
        }

        .day {
            color: var(--text-light);
            font-weight: 500;
        }

        .time {
            color: var(--primary-orange);
            font-weight: 600;
        }

        .status {
            display: inline-block;
            padding: 4px 12px;
            background: var(--success-green);
            color: var(--text-light);
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
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

        .support-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .contact-method {
                flex-direction: column;
                text-align: center;
                gap: 15px;
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
            
            .page-title {
                font-size: 2.2em;
            }
            
            .history-header {
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .support-card {
                padding: 25px 20px;
            }
            
            .card-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
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

        .char-counter {
            text-align: right;
            color: var(--text-gray);
            font-size: 0.9em;
            margin-top: 5px;
        }

        .char-counter.warning {
            color: var(--warning-yellow);
        }

        .char-counter.error {
            color: #ff6b6b;
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
                <li><a href="profile.php"><i class="fas fa-user"></i> Личный кабинет</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Корзина</a></li>
                <li><a href="support.php" class="active"><i class="fas fa-headset"></i> Поддержка</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> О сайте</a></li>
                <?php if (isset($_SESSION['admin']) || (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin')): ?>
                <li><a href="admin/index.php" style="color: var(--primary-orange);"><i class="fas fa-shield-alt"></i> Админ панель</a></li>
                <?php endif; ?>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="user-info">
                <i class="fas fa-user-circle"></i> Добро пожаловать, <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>!
            </div>

            <div class="page-header">
                <h1 class="page-title">Служба поддержки</h1>
                <p class="page-subtitle">Мы всегда готовы помочь вам с любыми вопросами. Выберите удобный способ связи или отправьте нам сообщение.</p>
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

            <div class="support-container">
                <div class="support-card">
                    <div class="card-header">
                        <i class="fas fa-phone-alt"></i>
                        <h2>Контактная информация</h2>
                    </div>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-info">
                                <div class="contact-title">Телефон поддержки</div>
                                <div class="contact-detail">+7 (999) 123-45-67</div>
                                <div class="contact-note">Бесплатно по России</div>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info">
                                <div class="contact-title">Электронная почта</div>
                                <div class="contact-detail">support@autosalon.ru</div>
                                <div class="contact-note">Ответ в течение 2 часов</div>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="contact-info">
                                <div class="contact-title">Онлайн-чат</div>
                                <div class="contact-detail">Доступен на сайте</div>
                                <div class="contact-note">Круглосуточно</div>
                            </div>
                        </div>
                    </div>

                    <div class="working-hours">
                        <div class="hours-header">
                            <i class="fas fa-clock"></i>
                            <h3>Часы работы поддержки</h3>
                        </div>
                        <div class="hours-grid">
                            <div class="hour-row">
                                <span class="day">Понедельник - Пятница</span>
                                <span class="time">9:00 - 18:00</span>
                            </div>
                            <div class="hour-row">
                                <span class="day">Суббота</span>
                                <span class="time">10:00 - 16:00</span>
                            </div>
                            <div class="hour-row">
                                <span class="day">Воскресенье</span>
                                <span class="time">Выходной</span>
                            </div>
                            <div class="hour-row">
                                <span class="day">Текущий статус</span>
                                <span class="status">Онлайн</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="support-card">
                    <div class="card-header">
                        <i class="fas fa-envelope"></i>
                        <h2>Обратная связь</h2>
                    </div>
                    
                    <form action="support.php" method="POST" class="feedback-form">
                        <input type="hidden" name="send_feedback" value="1">
                        
                        <div class="form-group">
                            <label for="subject">
                                <i class="fas fa-tag"></i>
                                Тема сообщения
                            </label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" 
                                   placeholder="Например: Вопрос по автомобилю BMW X5" required>
                        </div>

                        <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i>
                                Ваше сообщение
                            </label>
                            <textarea id="message" name="message" class="form-control" 
                                      placeholder="Опишите ваш вопрос или проблему подробно..." 
                                      required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            <div class="char-counter" id="charCounter">
                                <span id="charCount">0</span>/1000 символов
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                Отправить сообщение
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                                Очистить
                            </button>
                        </div>
                    </form>

                    <div class="feedback-history">
                        <h3 class="history-title">
                            <i class="fas fa-history"></i>
                            История ваших обращений
                        </h3>
                        
                        <?php if (empty($feedback_history)): ?>
                            <div class="empty-history">
                                <i class="fas fa-inbox"></i>
                                <p>У вас пока нет отправленных сообщений</p>
                            </div>
                        <?php else: ?>
                            <div class="history-list">
                                <?php foreach ($feedback_history as $feedback): ?>
                                <div class="history-item">
                                    <div class="history-header">
                                        <div class="history-subject"><?php echo htmlspecialchars($feedback['subject']); ?></div>
                                        <div class="history-status status-<?php echo $feedback['status']; ?>">
                                            <?php 
                                            $status_text = [
                                                'new' => 'Новое',
                                                'in_progress' => 'В работе', 
                                                'resolved' => 'Решено'
                                            ];
                                            echo $status_text[$feedback['status']];
                                            ?>
                                        </div>
                                    </div>
                                    <div class="history-message">
                                        <?php echo htmlspecialchars($feedback['message']); ?>
                                    </div>
                                    <div class="history-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d.m.Y H:i', strtotime($feedback['created_at'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
        // Счетчик символов для текстового поля
        const messageTextarea = document.getElementById('message');
        const charCounter = document.getElementById('charCounter');
        const charCount = document.getElementById('charCount');

        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            
            if (length > 900) {
                charCounter.classList.add('warning');
                charCounter.classList.remove('error');
            } else if (length > 1000) {
                charCounter.classList.add('error');
                charCounter.classList.remove('warning');
            } else {
                charCounter.classList.remove('warning', 'error');
            }
        });

        // Инициализация счетчика при загрузке
        charCount.textContent = messageTextarea.value.length;

        // Подтверждение очистки формы
        document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите очистить все поля формы?')) {
                e.preventDefault();
            }
        });

        // Валидация формы
        document.querySelector('form').addEventListener('submit', function(e) {
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (subject.length < 5) {
                e.preventDefault();
                alert('Тема сообщения должна содержать не менее 5 символов');
                return false;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Сообщение должно содержать не менее 10 символов');
                return false;
            }
            
            if (message.length > 1000) {
                e.preventDefault();
                alert('Сообщение не должно превышать 1000 символов');
                return false;
            }
            
            return true;
        });

        // Плавное появление элементов
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.support-card, .contact-method, .history-item');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Имитация онлайн статуса
        function updateOnlineStatus() {
            const now = new Date();
            const day = now.getDay();
            const hour = now.getHours();
            const statusElement = document.querySelector('.status');
            
            if (day === 0) { // Воскресенье
                statusElement.textContent = 'Оффлайн';
                statusElement.style.background = '#dc3545';
            } else if (day === 6) { // Суббота
                if (hour >= 10 && hour < 16) {
                    statusElement.textContent = 'Онлайн';
                    statusElement.style.background = '#28a745';
                } else {
                    statusElement.textContent = 'Оффлайн';
                    statusElement.style.background = '#dc3545';
                }
            } else { // Будни
                if (hour >= 9 && hour < 18) {
                    statusElement.textContent = 'Онлайн';
                    statusElement.style.background = '#28a745';
                } else {
                    statusElement.textContent = 'Оффлайн';
                    statusElement.style.background = '#dc3545';
                }
            }
        }

        // Обновляем статус при загрузке
        updateOnlineStatus();
    </script>

    <?php include 'chat_widget.php'; ?>
</body>
</html>