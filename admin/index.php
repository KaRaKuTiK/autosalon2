<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Получаем статистику
try {
    $totalCars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalSalons = $pdo->query("SELECT COUNT(*) FROM salons")->fetchColumn();
    $pendingFeedback = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'new'")->fetchColumn();
    $totalCartItems = $pdo->query("SELECT COUNT(*) FROM cart")->fetchColumn();
} catch (PDOException $e) {
    $totalCars = $totalUsers = $totalSalons = $pendingFeedback = $totalCartItems = 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - AutoSalon</title>
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

        .admin-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-name {
            color: var(--text-gray);
        }

        .admin-name strong {
            color: var(--primary-orange);
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

        .btn-logout {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .btn-logout:hover {
            background: rgba(220, 53, 69, 0.3);
        }

        .main-content {
            padding: 40px 0;
        }

        .page-title {
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            color: var(--text-gray);
            margin-bottom: 40px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 102, 0, 0.4);
        }

        .stat-icon {
            font-size: 2.5em;
            color: var(--primary-orange);
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 1em;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .menu-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-decoration: none;
            color: var(--text-light);
            transition: all 0.3s ease;
            text-align: center;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 102, 0, 0.4);
            box-shadow: 0 15px 40px rgba(255, 102, 0, 0.2);
        }

        .menu-icon {
            font-size: 3em;
            color: var(--primary-orange);
            margin-bottom: 20px;
        }

        .menu-title {
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .menu-description {
            color: var(--text-gray);
            font-size: 0.9em;
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
                <div class="admin-info">
                    <div class="admin-name">
                        <i class="fas fa-user-shield"></i> Администратор: <strong><?php echo htmlspecialchars($admin['full_name']); ?></strong>
                    </div>
                    <a href="logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Панель управления</h1>
            <p class="page-subtitle">Добро пожаловать в административную панель AutoSalon Premium</p>

            <div class="stats-container">
                <div class="stats-header">
                    <i class="fas fa-chart-line"></i> Статистика
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalUsers; ?></div>
                        <div class="stat-label">Пользователей</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalCars; ?></div>
                        <div class="stat-label">Автомобилей</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalSalons; ?></div>
                        <div class="stat-label">Салоны</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value"><?php echo $pendingFeedback; ?></div>
                        <div class="stat-label">Новых обращений</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalCartItems; ?></div>
                        <div class="stat-label">Товаров в корзине</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
