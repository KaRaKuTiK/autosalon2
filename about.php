<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О компании - AutoSalon Premium</title>
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

        .about-container {
            display: grid;
            gap: 40px;
            margin-bottom: 50px;
        }

        .hero-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 60px 40px;
            border-radius: 25px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), var(--hover-orange));
        }

        .hero-icon {
            font-size: 4em;
            color: var(--primary-orange);
            margin-bottom: 20px;
            filter: drop-shadow(0 0 20px rgba(255, 102, 0, 0.3));
        }

        .hero-title {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .hero-description {
            font-size: 1.3em;
            color: var(--text-gray);
            line-height: 1.7;
            max-width: 800px;
            margin: 0 auto 30px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .feature-card {
            background: rgba(40, 40, 40, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-orange);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 102, 0, 0.3);
            box-shadow: 0 15px 40px rgba(255, 102, 0, 0.1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            font-size: 3em;
            color: var(--primary-orange);
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.4em;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .feature-description {
            color: var(--text-gray);
            line-height: 1.6;
        }

        .stats-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 50px 40px;
            border-radius: 25px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .stats-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .stats-title {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .stats-title i {
            color: var(--primary-orange);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .stat-card {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255, 102, 0, 0.1);
            border: 1px solid rgba(255, 102, 0, 0.2);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 102, 0, 0.4);
            box-shadow: 0 10px 30px rgba(255, 102, 0, 0.2);
        }

        .stat-icon {
            font-size: 2.5em;
            color: var(--primary-orange);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 1.1em;
            font-weight: 500;
        }

        .mission-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        @media (max-width: 968px) {
            .mission-section {
                grid-template-columns: 1fr;
            }
        }

        .mission-content {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 25px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .mission-title {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .mission-title i {
            color: var(--primary-orange);
        }

        .mission-text {
            color: var(--text-gray);
            line-height: 1.7;
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .mission-highlight {
            background: rgba(255, 102, 0, 0.1);
            border: 1px solid rgba(255, 102, 0, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
        }

        .highlight-text {
            color: var(--primary-orange);
            font-size: 1.2em;
            font-weight: 600;
            text-align: center;
            line-height: 1.6;
        }

        .values-grid {
            display: grid;
            gap: 20px;
        }

        .value-item {
            background: rgba(40, 40, 40, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .value-item:hover {
            border-color: rgba(255, 102, 0, 0.3);
            transform: translateX(10px);
        }

        .value-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .value-icon {
            font-size: 1.8em;
            color: var(--primary-orange);
        }

        .value-title {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--text-light);
        }

        .value-description {
            color: var(--text-gray);
            line-height: 1.6;
        }

        .team-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 50px 40px;
            border-radius: 25px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .team-title {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 40px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .team-title i {
            color: var(--primary-orange);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .team-member {
            background: rgba(40, 40, 40, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 102, 0, 0.3);
            box-shadow: 0 15px 40px rgba(255, 102, 0, 0.1);
        }

        .member-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-orange), var(--hover-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5em;
            color: var(--text-light);
            margin: 0 auto 20px;
        }

        .member-name {
            font-size: 1.4em;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-light);
        }

        .member-role {
            color: var(--primary-orange);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .member-description {
            color: var(--text-gray);
            line-height: 1.6;
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

        .hero-section, .feature-card, .stat-card, .mission-content, .value-item, .team-member {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 25px;
            }
            
            .hero-title {
                font-size: 2em;
            }
            
            .hero-description {
                font-size: 1.1em;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .mission-content, .team-section, .stats-section {
                padding: 30px 20px;
            }
            
            .feature-card, .value-item, .team-member {
                padding: 20px;
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

        .car-4 {
            bottom: 40%;
            left: 10%;
            animation-delay: 1s;
        }

        .car-5 {
            top: 60%;
            left: 15%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Специальные стили для учебного проекта */
        .educational-notice {
            background: linear-gradient(135deg, rgba(255, 102, 0, 0.15), rgba(255, 102, 0, 0.1));
            border: 2px solid rgba(255, 102, 0, 0.3);
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .educational-notice::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), var(--hover-orange));
        }

        .notice-icon {
            font-size: 3em;
            color: var(--primary-orange);
            margin-bottom: 20px;
        }

        .notice-title {
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .notice-text {
            color: var(--text-gray);
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .tech-stack {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .tech-badge {
            background: rgba(255, 102, 0, 0.2);
            color: var(--primary-orange);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            border: 1px solid rgba(255, 102, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Декоративные плавающие иконки машин -->
    <div class="floating-car car-1">🚗</div>
    <div class="floating-car car-2">🏎️</div>
    <div class="floating-car car-3">🚙</div>
    <div class="floating-car car-4">🚘</div>
    <div class="floating-car car-5">🚕</div>

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
                <li><a href="support.php"><i class="fas fa-headset"></i> Поддержка</a></li>
                <li><a href="about.php" class="active"><i class="fas fa-info-circle"></i> О сайте</a></li>
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
                <h1 class="page-title">О нашей компании</h1>
                <p class="page-subtitle">Узнайте больше о AutoSalon Premium - вашем надежном партнере в мире автомобилей</p>
            </div>

            <div class="about-container">
                <!-- Герой-секция -->
                <div class="hero-section">
                    <div class="hero-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <h2 class="hero-title">AutoSalon Premium</h2>
                    <p class="hero-description">
                        Мы - современный автосалон, предлагающий широкий выбор автомобилей различных марок и моделей. 
                        Наша цель - помочь вам найти идеальный автомобиль, который соответствует вашим потребностям и бюджету.
                    </p>
                    
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="feature-title">Гарантия качества</h3>
                            <p class="feature-description">Все автомобили проходят тщательную проверку и имеют официальную гарантию</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3 class="feature-title">Индивидуальный подход</h3>
                            <p class="feature-description">Мы находим оптимальные решения для каждого клиента</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3 class="feature-title">Круглосуточная поддержка</h3>
                            <p class="feature-description">Всегда готовы помочь с выбором и ответить на вопросы</p>
                        </div>
                    </div>
                </div>

                <!-- Статистика -->
                <div class="stats-section">
                    <div class="stats-header">
                        <h2 class="stats-title">
                            <i class="fas fa-chart-line"></i>
                            AutoSalon в цифрах
                        </h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Автомобилей в наличии</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number">10,000+</div>
                            <div class="stat-label">Довольных клиентов</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-number">15+</div>
                            <div class="stat-label">Лет на рынке</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="stat-number">25+</div>
                            <div class="stat-label">Городов присутствия</div>
                        </div>
                    </div>
                </div>

                <!-- Миссия и ценности -->
                <div class="mission-section">
                    <div class="mission-content">
                        <h2 class="mission-title">
                            <i class="fas fa-bullseye"></i>
                            Наша миссия
                        </h2>
                        <p class="mission-text">
                            Мы стремимся сделать процесс покупки автомобиля максимально простым, прозрачным и приятным 
                            для каждого клиента. Наша команда профессионалов готова предоставить вам лучший сервис 
                            и поддержку на всех этапах сотрудничества.
                        </p>
                        <p class="mission-text">
                            Мы верим, что правильный автомобиль может изменить жизнь к лучшему, и мы здесь, 
                            чтобы помочь вам найти именно тот автомобиль, который станет вашим надежным спутником.
                        </p>
                        
                        <div class="mission-highlight">
                            <p class="highlight-text">
                                "Качество - это не случайность, это результат упорного труда, разумного руководства, 
                                умелого исполнения и мудрого выбора."
                            </p>
                        </div>
                    </div>
                    
                    <div class="values-grid">
                        <div class="value-item">
                            <div class="value-header">
                                <i class="fas fa-star value-icon"></i>
                                <h3 class="value-title">Качество</h3>
                            </div>
                            <p class="value-description">Мы предлагаем только проверенные автомобили от надежных производителей с полной сервисной историей.</p>
                        </div>
                        
                        <div class="value-item">
                            <div class="value-header">
                                <i class="fas fa-hand-holding-usd value-icon"></i>
                                <h3 class="value-title">Доступность</h3>
                            </div>
                            <p class="value-description">Широкий выбор автомобилей в разных ценовых категориях и гибкие условия покупки.</p>
                        </div>
                        
                        <div class="value-item">
                            <div class="value-header">
                                <i class="fas fa-clock value-icon"></i>
                                <h3 class="value-title">Надежность</h3>
                            </div>
                            <p class="value-description">Гарантия на все автомобили и круглосуточная техническая поддержка после покупки.</p>
                        </div>
                        
                        <div class="value-item">
                            <div class="value-header">
                                <i class="fas fa-heart value-icon"></i>
                                <h3 class="value-title">Забота о клиенте</h3>
                            </div>
                            <p class="value-description">Индивидуальный подход к каждому клиенту и полное сопровождение на всех этапах.</p>
                        </div>
                    </div>
                </div>

                <!-- Команда -->
                <div class="team-section">
                    <h2 class="team-title">
                        <i class="fas fa-users"></i>
                        Наша команда
                    </h2>
                    <div class="team-grid">
                        <div class="team-member">
                            <div class="member-avatar">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h3 class="member-name">Александр Петров</h3>
                            <div class="member-role">Генеральный директор</div>
                            <p class="member-description">15+ лет опыта в автомобильном бизнесе. Основатель AutoSalon Premium.</p>
                        </div>
                        
                        <div class="team-member">
                            <div class="member-avatar">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <h3 class="member-name">Мария Иванова</h3>
                            <div class="member-role">Менеджер по продажам</div>
                            <p class="member-description">Специалист с 8-летним опытом. Поможет подобрать идеальный автомобиль.</p>
                        </div>
                        
                        <div class="team-member">
                            <div class="member-avatar">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3 class="member-name">Дмитрий Сидоров</h3>
                            <div class="member-role">Технический специалист</div>
                            <p class="member-description">Сертифицированный эксперт по проверке и обслуживанию автомобилей.</p>
                        </div>
                    </div>
                </div>

                <!-- Уведомление об учебном проекте -->
                <div class="educational-notice">
                    <div class="notice-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="notice-title">Учебный проект</h3>
                    <p class="notice-text">
                        Данный сайт является учебным проектом, созданным для изучения веб-разработки. 
                        Здесь демонстрируются современные подходы к созданию веб-приложений с использованием 
                        популярных технологий и фреймворков.
                    </p>
                    <div class="tech-stack">
                        <span class="tech-badge">HTML5</span>
                        <span class="tech-badge">CSS3</span>
                        <span class="tech-badge">JavaScript</span>
                        <span class="tech-badge">PHP</span>
                        <span class="tech-badge">MySQL</span>
                        <span class="tech-badge">Responsive Design</span>
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
        // Анимация появления элементов при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.hero-section, .feature-card, .stat-card, .mission-content, .value-item, .team-member, .educational-notice');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            animatedElements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'all 0.6s ease-out';
                observer.observe(element);
            });

            // Анимация счетчиков статистики
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        stat.textContent = target + '+';
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(current) + '+';
                    }
                }, 50);
            });
        });
    </script>

    <?php include 'chat_widget.php'; ?>
</body>
</html>