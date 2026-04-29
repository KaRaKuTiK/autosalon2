<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

// Обработка фильтров
$type_filter = $_GET['type'] ?? '';

// Построение запроса с учетом фильтров
$sql = "SELECT cars.*, (SELECT image_path FROM car_images WHERE car_id = cars.id AND is_main = 1 LIMIT 1) as main_image FROM cars";
$params = [];

if (!empty($type_filter)) {
    $sql .= " WHERE type = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY price ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог автомобилей - AutoSalon Premium</title>
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

        .filters-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .filters-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filters-title {
            color: var(--text-light);
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filters-title i {
            color: var(--primary-orange);
        }

        .results-count {
            color: var(--text-gray);
            font-size: 1em;
            background: rgba(255, 102, 0, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
        }

        .filter-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 12px 24px;
            background: rgba(40, 40, 40, 0.8);
            color: var(--text-light);
            border: 1px solid var(--border-dark);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background: rgba(255, 102, 0, 0.1);
            border-color: rgba(255, 102, 0, 0.3);
            color: var(--primary-orange);
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: var(--primary-orange);
            color: var(--text-light);
            border-color: var(--primary-orange);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .car-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 102, 0, 0.2);
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .car-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-orange), var(--hover-orange));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .car-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(255, 102, 0, 0.2);
            border-color: rgba(255, 102, 0, 0.4);
        }

        .car-card:hover::before {
            transform: scaleX(1);
        }

        .car-image {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 4em;
            position: relative;
            overflow: hidden;
        }

        .car-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 102, 0, 0.1) 0%, transparent 50%);
        }

        .car-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-orange);
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .car-info {
            padding: 25px;
        }

        .car-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .car-title {
            color: var(--text-light);
            font-size: 1.4em;
            font-weight: 600;
            line-height: 1.3;
        }

        .car-price {
            color: var(--primary-orange);
            font-size: 1.6em;
            font-weight: 700;
            text-align: right;
        }

        .car-details {
            margin-bottom: 20px;
        }

        .car-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: var(--text-gray);
            font-size: 0.95em;
        }

        .car-detail i {
            color: var(--primary-orange);
            width: 16px;
        }

        .car-description {
            color: var(--text-gray);
            font-size: 0.9em;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .car-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9em;
        }

        .btn-primary {
            background: var(--primary-orange);
            color: var(--text-light);
            flex: 1;
        }

        .btn-primary:hover {
            background: var(--hover-orange);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-orange);
            border: 1px solid var(--primary-orange);
        }

        .btn-secondary:hover {
            background: rgba(255, 102, 0, 0.1);
            transform: translateY(-2px);
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

        .car-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Адаптивность */
        @media (max-width: 1200px) {
            .cars-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                gap: 5px;
            }
            
            .nav-links a {
                padding: 10px 15px;
                font-size: 0.9em;
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .page-title {
                font-size: 2.2em;
            }
            
            .filter-buttons {
                justify-content: center;
            }
            
            .filters-header {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .car-actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }

        /* Дополнительные декоративные элементы */
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
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Стили для типа автомобиля */
        .car-type {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255, 102, 0, 0.1);
            color: var(--primary-orange);
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
            border: 1px solid rgba(255, 102, 0, 0.3);
        }

        /* Счетчик автомобилей */
        .cars-count {
            text-align: center;
            color: var(--text-gray);
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .cars-count strong {
            color: var(--primary-orange);
        }
        .search-input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid var(--border-dark);
            border-radius: 15px;
            color: var(--text-light);
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 15px rgba(255, 102, 0, 0.2);
            background: rgba(50, 50, 50, 0.9);
        }
    </style>
</head>
<body>
    <!-- Декоративные плавающие иконки машин -->
    <div class="floating-car car-1">🚗</div>
    <div class="floating-car car-2">🏎️</div>
    <div class="floating-car car-3">🚙</div>
    <div class="floating-car car-4">🚘</div>

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
                <li><a href="dashboard.php" class="active"><i class="fas fa-car"></i> Каталог</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Личный кабинет</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Корзина</a></li>
                <li><a href="support.php"><i class="fas fa-headset"></i> Поддержка</a></li>
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
                <h1 class="page-title">Каталог автомобилей</h1>
                <p class="page-subtitle">Найдите автомобиль своей мечты</p>
            </div>

            <div class="filters-section">
                <div class="filters-header">
                    <h2 class="filters-title">
                        <i class="fas fa-filter"></i> Фильтр по типу автомобиля
                    </h2>
                    <div class="results-count">
                        <i class="fas fa-car"></i> Найдено автомобилей: <strong><?php echo count($cars); ?></strong>
                    </div>
                </div>
                <div class="search-container" style="margin-bottom: 25px;">
                    <input type="text" id="searchInput" placeholder="Поиск по названию..." class="search-input">
                </div>
                <div class="filter-buttons">
                    <a href="dashboard.php" class="filter-btn <?php echo empty($type_filter) ? 'active' : ''; ?>">
                        <i class="fas fa-all"></i> Все автомобили
                    </a>
                    <a href="dashboard.php?type=легковая" class="filter-btn <?php echo $type_filter == 'легковая' ? 'active' : ''; ?>">
                        <i class="fas fa-car"></i> Легковые
                    </a>
                    <a href="dashboard.php?type=внедорожник" class="filter-btn <?php echo $type_filter == 'внедорожник' ? 'active' : ''; ?>">
                        <i class="fas fa-truck-monster"></i> Внедорожники
                    </a>
                    <a href="dashboard.php?type=спорткар" class="filter-btn <?php echo $type_filter == 'спорткар' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Спорткары
                    </a>
                </div>
            </div>

            <div class="cars-count">
                Показано <strong><?php echo count($cars); ?></strong> автомобилей
                <?php if (!empty($type_filter)): ?>
                    в категории "<strong><?php echo htmlspecialchars($type_filter); ?></strong>"
                <?php endif; ?>
            </div>

            <div class="cars-grid">
                <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <div class="car-image">
                        <?php 
                        if (!empty($car['main_image'])) {
                            echo '<img src="' . htmlspecialchars($car['main_image']) . '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top:0; left:0; z-index: 1;">';
                        } else {
                            $carImages = [
                                'Toyota' => '🚗',
                                'BMW' => '🚙', 
                                'Porsche' => '🏎️',
                                'Honda' => '🚘',
                                'Audi' => '🚗',
                                'Ferrari' => '🏎️',
                                'Kia' => '🚗',
                                'Land Rover' => '🚙'
                            ];
                            echo $carImages[$car['brand']] ?? '🚗';
                        }

                        $carIcons = [
                            'Toyota' => 'fas fa-car',
                            'BMW' => 'fas fa-truck-monster',
                            'Porsche' => 'fas fa-tachometer-alt',
                            'Honda' => 'fas fa-car',
                            'Audi' => 'fas fa-car',
                            'Ferrari' => 'fas fa-tachometer-alt',
                            'Kia' => 'fas fa-car',
                            'Land Rover' => 'fas fa-truck-monster'
                        ];
                        ?>
                        <?php
                        // Удален вывод оранжевого бейджа поверх карточки, как и просил клиент
                        ?>
                    </div>
                    <div class="car-info">
                        <div class="car-header">
                            <h3 class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                            <div class="car-price"><?php echo number_format($car['price'], 0, ',', ' '); ?> ₽</div>
                        </div>
                        <div class="car-details">
                            <div class="car-detail">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Год выпуска: <strong><?php echo htmlspecialchars($car['year']); ?></strong></span>
                            </div>
                            <div class="car-detail">
                                <i class="fas fa-palette"></i>
                                <span>Цвет: <strong><?php 
                                    $colorMap = [
                                        '🟢' => 'Зеленый', '🔴' => 'Красный', '🔵' => 'Синий',
                                        '⚫' => 'Черный', '⚪' => 'Белый', '🟡' => 'Желтый',
                                        '🟠' => 'Оранжевый', '🟣' => 'Фиолетовый', '🟤' => 'Коричневый',
                                        '🟥' => 'Красный', '🟦' => 'Синий', '🟩' => 'Зеленый',
                                        '🟧' => 'Оранжевый', '🟨' => 'Желтый', '🟪' => 'Фиолетовый', '🟫' => 'Коричневый'
                                    ];
                                    $colorText = strtr($car['color'], $colorMap);
                                    echo htmlspecialchars($colorText ?: 'Не указан'); 
                                ?></strong></span>
                            </div>
                            <div class="car-detail">
                                <i class="fas fa-tag"></i>
                                <span>Тип: <span class="car-type"><?php echo htmlspecialchars($car['type']); ?></span></span>
                            </div>
                        </div>
                        <p class="car-description"><?php echo htmlspecialchars($car['description']); ?></p>
                        <div class="car-actions">
                            <form action="add_to_cart.php" method="POST" style="display: inline;">
    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-shopping-cart"></i> В корзину
    </button>
</form>
                            <a href="#" class="btn btn-secondary">
                                <i class="fas fa-heart"></i> В избранное
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
        // Добавляем анимацию появления карточек с задержкой
        document.addEventListener('DOMContentLoaded', function() {
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // Плавная прокрутка к фильтрам
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('dashboard.php')) {
                    // e.preventDefault();
                }
            });
        });

        // Поиск по названию (клиентская фильтрация)
        const searchInput = document.getElementById('searchInput');
        const carCards = document.querySelectorAll('.car-card');
        const carsCountElement = document.querySelector('.results-count strong');
        const totalCarsCount = document.querySelector('.cars-count strong');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                let visibleCount = 0;

                carCards.forEach(card => {
                    // Ищем название машины (марка + модель)
                    const carName = card.querySelector('.car-title').textContent.toLowerCase();
                    if (carName.includes(query)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Обновляем счетчики только визуально
                if (carsCountElement) carsCountElement.textContent = visibleCount;
                if (totalCarsCount) totalCarsCount.textContent = visibleCount;

                // Управление сообщением "Не найдено"
                let noResultsMsg = document.getElementById('noResultsMessage');
                if (visibleCount === 0) {
                    if (!noResultsMsg) {
                        const grid = document.querySelector('.cars-grid');
                        noResultsMsg = document.createElement('div');
                        noResultsMsg.id = 'noResultsMessage';
                        noResultsMsg.className = 'empty-state';
                        noResultsMsg.innerHTML = '<i class="fas fa-search" style="font-size: 4em; color: var(--primary-orange); margin-bottom: 20px; opacity: 0.5; display:block; text-align:center;"></i><h3 style="text-align:center; color: var(--text-light); font-size: 1.5em;">Автомобили не найдены</h3>';
                        grid.parentNode.insertBefore(noResultsMsg, grid.nextSibling);
                    }
                    noResultsMsg.style.display = 'block';
                } else {
                    if (noResultsMsg) {
                        noResultsMsg.style.display = 'none';
                    }
                }
            });
        }
    </script>

    <?php include 'chat_widget.php'; ?>
</body>
</html>