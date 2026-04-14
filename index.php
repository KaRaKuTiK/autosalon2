<?php
session_start();
require_once 'config.php';

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Автосалон Premium - Вход в систему</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
            display: flex;
            flex-direction: column;
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
            padding: 25px 0;
            border-bottom: 2px solid var(--primary-orange);
            box-shadow: 0 4px 20px rgba(255, 102, 0, 0.2);
            position: relative;
            z-index: 100;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .logo-icon {
            font-size: 2.8em;
            color: var(--primary-orange);
            filter: drop-shadow(0 0 10px rgba(255, 102, 0, 0.5));
        }

        .logo-text {
            color: var(--primary-orange);
            font-size: 2.8em;
            font-weight: 700;
            text-shadow: 0 0 20px rgba(255, 102, 0, 0.4);
        }

        .logo-text span {
            color: var(--text-light);
            font-weight: 300;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
            position: relative;
        }

        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 50px;
            max-width: 1000px;
            width: 100%;
        }

        .welcome-section {
            flex: 1;
            text-align: left;
            padding: 30px;
        }

        .welcome-title {
            font-size: 3.2em;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }

        .welcome-subtitle {
            font-size: 1.3em;
            color: var(--text-gray);
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 300;
        }

        .features {
            list-style: none;
            margin-top: 40px;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 1.1em;
            color: var(--text-gray);
        }

        .features i {
            color: var(--primary-orange);
            font-size: 1.3em;
            width: 25px;
        }

        .auth-form {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(15px);
            padding: 50px 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.3);
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 102, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
        }

        .auth-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-orange), var(--hover-orange));
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            color: var(--text-light);
            font-size: 2.2em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-gray);
            font-size: 1em;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.95em;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            font-size: 1.1em;
            transition: color 0.3s ease;
        }

        .input-with-icon input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid var(--border-dark);
            border-radius: 10px;
            color: var(--text-light);
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .input-with-icon input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
            background: rgba(50, 50, 50, 0.8);
        }

        .input-with-icon input:focus + i {
            color: var(--primary-orange);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-orange), var(--hover-orange));
            color: var(--text-light);
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
            background: linear-gradient(135deg, var(--hover-orange), var(--light-orange));
        }

        .btn:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .register-link p {
            color: var(--text-gray);
            margin-bottom: 15px;
        }

        .register-btn {
            display: inline-block;
            padding: 12px 30px;
            background: transparent;
            color: var(--primary-orange);
            border: 2px solid var(--primary-orange);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-btn:hover {
            background: rgba(255, 102, 0, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.2);
        }

        .footer {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            padding: 25px 0;
            border-top: 1px solid rgba(255, 102, 0, 0.3);
            margin-top: auto;
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

        .auth-form, .welcome-section {
            animation: fadeInUp 0.8s ease-out;
        }

        .welcome-section {
            animation-delay: 0.2s;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .welcome-section {
                text-align: center;
                padding: 20px;
            }
            
            .welcome-title {
                font-size: 2.5em;
            }
            
            .auth-form {
                max-width: 100%;
                padding: 40px 30px;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Дополнительные декоративные элементы */
        .floating-car {
            position: absolute;
            font-size: 3em;
            opacity: 0.1;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
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

    <div class="main-content">
        <div class="container">
            <div class="auth-container">
                <div class="welcome-section">
                    <h1 class="welcome-title">Добро пожаловать в мир премиальных автомобилей</h1>
                    <p class="welcome-subtitle">Войдите в систему, чтобы получить доступ к эксклюзивной коллекции автомобилей и персонализированным услугам нашего автосалона.</p>
                    
                    <ul class="features">
                        <li><i class="fas fa-check-circle"></i> Широкий выбор премиальных автомобилей</li>
                        <li><i class="fas fa-check-circle"></i> Персональные консультации</li>
                        <li><i class="fas fa-check-circle"></i> Лучшие условия покупки</li>
                        <li><i class="fas fa-check-circle"></i> Техническая поддержка 24/7</li>
                    </ul>
                </div>

                <div class="auth-form">
                    <div class="form-header">
                        <h2>Вход в систему</h2>
                        <p>Введите свои учетные данные</p>
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

                    <form action="login.php" method="POST">
                        <div class="form-group">
                            <label for="login">Имя пользователя</label>
                            <div class="input-with-icon">
                                <input type="text" id="login" name="login" required placeholder="Введите ваш логин">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <div class="input-with-icon">
                                <input type="password" id="password" name="password" required placeholder="Введите ваш пароль">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-sign-in-alt"></i> Войти в систему
                        </button>
                    </form>
                    
                    <div class="register-link">
                        <p>Еще не с нами?</p>
                        <a href="register.php" class="register-btn">
                            <i class="fas fa-user-plus"></i> Создать аккаунт
                        </a>
                        <div style="margin-top: 25px;">
                            <a href="admin_login.php" style="color: var(--text-gray); text-decoration: none; font-size: 0.9em; transition: color 0.3s;" onmouseover="this.style.color='var(--primary-orange)'" onmouseout="this.style.color='var(--text-gray)'">
                                <i class="fas fa-user-shield"></i> Я админ
                            </a>
                        </div>
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
        // Добавляем плавную анимацию для элементов формы при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
    </script>
</body>
</html>