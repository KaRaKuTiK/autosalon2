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
    <title>Автосалон Premium - Регистрация</title>
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
            padding: 40px 0;
            position: relative;
        }

        .register-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 50px;
            max-width: 1100px;
            width: 100%;
        }

        .welcome-section {
            flex: 1;
            text-align: left;
            padding: 30px;
        }

        .welcome-title {
            font-size: 2.8em;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }

        .welcome-subtitle {
            font-size: 1.2em;
            color: var(--text-gray);
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 300;
        }

        .benefits {
            list-style: none;
            margin-top: 30px;
        }

        .benefits li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 18px;
            font-size: 1.05em;
            color: var(--text-gray);
        }

        .benefits i {
            color: var(--primary-orange);
            font-size: 1.2em;
            width: 25px;
        }

        .register-form {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(15px);
            padding: 45px 35px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.3);
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 102, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 480px;
            position: relative;
            overflow: hidden;
        }

        .register-form::before {
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
            margin-bottom: 35px;
        }

        .form-header h2 {
            color: var(--text-light);
            font-size: 2em;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-gray);
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.92em;
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

        .input-with-icon input,
        .input-with-icon select {
            width: 100%;
            padding: 14px 15px 14px 45px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid var(--border-dark);
            border-radius: 10px;
            color: var(--text-light);
            font-size: 1em;
            transition: all 0.3s ease;
            appearance: none;
        }

        .input-with-icon select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23b0b0b0' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }

        .input-with-icon input:focus,
        .input-with-icon select:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.2);
            background: rgba(50, 50, 50, 0.8);
        }

        .input-with-icon input:focus + i,
        .input-with-icon select:focus + i {
            color: var(--primary-orange);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
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
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.4);
            background: linear-gradient(135deg, var(--hover-orange), var(--light-orange));
        }

        .btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-link p {
            color: var(--text-gray);
            margin-bottom: 12px;
        }

        .login-btn {
            display: inline-block;
            padding: 10px 25px;
            background: transparent;
            color: var(--primary-orange);
            border: 2px solid var(--primary-orange);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
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

        .register-form, .welcome-section {
            animation: fadeInUp 0.8s ease-out;
        }

        .welcome-section {
            animation-delay: 0.2s;
        }

        /* Адаптивность */
        @media (max-width: 968px) {
            .register-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .welcome-section {
                text-align: center;
                padding: 20px;
            }
            
            .welcome-title {
                font-size: 2.2em;
            }
            
            .register-form {
                max-width: 100%;
                padding: 35px 25px;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-form {
                padding: 25px 20px;
            }
        }

        /* Дополнительные декоративные элементы */
        .floating-car {
            position: absolute;
            font-size: 2.5em;
            opacity: 0.1;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }

        .car-1 {
            top: 15%;
            left: 8%;
            animation-delay: 0s;
        }

        .car-2 {
            bottom: 25%;
            right: 10%;
            animation-delay: 2s;
        }

        .car-3 {
            top: 60%;
            left: 12%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
        }

        /* Стили для валидации */
        .input-with-icon input:valid:not(:placeholder-shown) {
            border-color: #28a745;
        }

        .input-with-icon input:invalid:not(:placeholder-shown) {
            border-color: #dc3545;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 0.8em;
            color: var(--text-gray);
        }

        .progress-bar {
            height: 4px;
            background: var(--border-dark);
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: var(--primary-orange);
            width: 0%;
            transition: width 0.3s ease;
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
            <div class="register-container">
                <div class="welcome-section">
                    <h1 class="welcome-title">Присоединяйтесь к нашему автоклубу</h1>
                    <p class="welcome-subtitle">Создайте учетную запись, чтобы получить доступ к эксклюзивным предложениям, персонализированным рекомендациям и специальным условиям покупки.</p>
                    
                    <ul class="benefits">
                        <li><i class="fas fa-star"></i> Доступ к эксклюзивным автомобилям</li>
                        <li><i class="fas fa-percentage"></i> Специальные условия финансирования</li>
                        <li><i class="fas fa-clock"></i> Приоритетное обслуживание</li>
                        <li><i class="fas fa-gift"></i> Персональные предложения и акции</li>
                        <li><i class="fas fa-headset"></i> Круглосуточная поддержка</li>
                    </ul>
                </div>

                <div class="register-form">
                    <div class="form-header">
                        <h2>Создать аккаунт</h2>
                        <p>Заполните информацию для регистрации</p>
                    </div>
                    
                    <?php
                    if (isset($_SESSION['error'])) {
                        echo '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>

                    <form action="register_process.php" method="POST" id="registrationForm">
                        <div class="form-group">
                            <label for="full_name">Полное имя</label>
                            <div class="input-with-icon">
                                <input type="text" id="full_name" name="full_name" required placeholder="Введите ваше ФИО">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="login">Имя пользователя</label>
                            <div class="input-with-icon">
                                <input type="text" id="login" name="login" required placeholder="Придумайте логин">
                                <i class="fas fa-at"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email адрес</label>
                            <div class="input-with-icon">
                                <input type="email" id="email" name="email" required placeholder="your@email.com">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <div class="input-with-icon">
                                <input type="password" id="password" name="password" required placeholder="Создайте надежный пароль">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="password-strength">
                                Надежность пароля: <span id="password-strength-text">слабый</span>
                                <div class="progress-bar">
                                    <div class="progress" id="password-strength-bar"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="age">Возраст</label>
                                <div class="input-with-icon">
                                    <input type="number" id="age" name="age" min="18" max="120" required placeholder="18">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Пол</label>
                                <div class="input-with-icon">
                                    <select id="gender" name="gender" required>
                                        <option value="">Выберите пол</option>
                                        <option value="male">Мужской</option>
                                        <option value="female">Женский</option>
                                    </select>
                                    <i class="fas fa-venus-mars"></i>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-user-plus"></i> Создать аккаунт
                        </button>
                    </form>
                    
                    <div class="login-link">
                        <p>Уже есть учетная запись?</p>
                        <a href="index.php" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Войти в систему
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
        // Индикатор сложности пароля
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            let strength = 0;
            let color = '#dc3545';
            let text = 'Слабый';
            
            if (password.length >= 8) strength += 25;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
            if (password.match(/\d/)) strength += 25;
            if (password.match(/[^a-zA-Z\d]/)) strength += 25;
            
            if (strength >= 75) {
                color = '#28a745';
                text = 'Сильный';
            } else if (strength >= 50) {
                color = '#ffc107';
                text = 'Средний';
            } else if (strength >= 25) {
                color = '#fd7e14';
                text = 'Слабый';
            } else {
                color = '#dc3545';
                text = 'Очень слабый';
            }
            
            strengthBar.style.width = strength + '%';
            strengthBar.style.background = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });

        // Плавная анимация для элементов формы
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    if (!this.value && this.tagName !== 'SELECT') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });

        // Валидация формы
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const age = document.getElementById('age').value;
            if (age < 18) {
                e.preventDefault();
                alert('Для регистрации вам должно быть не менее 18 лет');
                return false;
            }
            return true;
        });
    </script>
</body>
</html>