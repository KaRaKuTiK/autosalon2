<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Статистика пользователей
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
    
    // Проверяем наличие колонок перед выполнением
    $colsStmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $colsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $blockedUsers = 0;
    if (in_array('is_blocked', $columns)) {
        $blockedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_blocked = 1")->fetchColumn() ?: 0;
    }
    
    $deletedUsers = 0;
    if (in_array('is_deleted', $columns)) {
        $deletedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_deleted = 1")->fetchColumn() ?: 0;
    }
    
    $activeUsers = $totalUsers - $blockedUsers - $deletedUsers;

    $onlineUsers = 0;
    if (in_array('last_activity', $columns)) {
        $onlineUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE last_activity >= NOW() - INTERVAL 5 MINUTE")->fetchColumn() ?: 0;
    }
    $offlineUsers = $totalUsers - $onlineUsers;

    // Конверсия
    $usersWithFavorites = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM favorites")->fetchColumn() ?: 0;
    $usersWithoutFavorites = $totalUsers - $usersWithFavorites;

    $usersWithCart = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM cart")->fetchColumn() ?: 0;
    $usersWithoutCart = $totalUsers - $usersWithCart;

    $usersWithTestDrives = 0;
    $tdColsStmt = $pdo->query("SHOW COLUMNS FROM test_drive_requests");
    if (in_array('user_id', $tdColsStmt->fetchAll(PDO::FETCH_COLUMN))) {
        $usersWithTestDrives = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM test_drive_requests WHERE user_id IS NOT NULL")->fetchColumn() ?: 0;
    }
    $usersWithoutTestDrives = $totalUsers - $usersWithTestDrives;

    // Динамика регистраций (последние 7 дней)
    $dates = [];
    $regCounts = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = $date;
        $regCounts[$date] = 0;
    }

    if (in_array('created_at', $columns)) {
        $regsStmt = $pdo->query("
            SELECT DATE(created_at) as reg_date, COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE(NOW()) - INTERVAL 6 DAY
            GROUP BY DATE(created_at)
        ");
        $registrations = $regsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($registrations as $reg) {
            if (isset($regCounts[$reg['reg_date']])) {
                $regCounts[$reg['reg_date']] = $reg['count'];
            }
        }
    }

    // Топ-5 авто в избранном
    $topFavsStmt = $pdo->query("
        SELECT c.brand, c.model, COUNT(f.id) as fav_count 
        FROM favorites f
        JOIN cars c ON f.car_id = c.id
        GROUP BY c.id
        ORDER BY fav_count DESC
        LIMIT 5
    ");
    $topFavorites = $topFavsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fallback if errors
    $totalUsers = $blockedUsers = $deletedUsers = $activeUsers = 0;
    $onlineUsers = $offlineUsers = 0;
    $usersWithFavorites = $usersWithoutFavorites = 0;
    $usersWithCart = $usersWithoutCart = 0;
    $usersWithTestDrives = $usersWithoutTestDrives = 0;
    $dates = [];
    $regCounts = [];
    $topFavorites = [];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Новые стили для статистики */
        .stats-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card-new {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card-new:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 102, 0, 0.4);
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .stat-value.orange { color: var(--primary-orange); }
        .stat-value.red { color: #ff4d4d; }
        .stat-value.green { color: #4CAF50; }
        .stat-value.gray { color: #888; }

        .stat-label {
            color: var(--text-gray);
            font-size: 1em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .charts-row {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .chart-box {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            flex: 1;
            min-width: 300px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .chart-box h3 {
            text-align: center;
            margin-bottom: 15px;
            color: var(--primary-orange);
            font-size: 1.2em;
        }

        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }

        .bottom-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .line-chart-box {
            flex: 2;
            min-width: 500px;
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .line-chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .top-cars-box {
            flex: 1;
            min-width: 300px;
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .top-cars-box h3 {
            color: var(--primary-orange);
            margin-bottom: 15px;
            text-align: center;
        }

        .top-car-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .top-car-item:last-child {
            border-bottom: none;
        }

        .car-name {
            font-weight: 500;
        }

        .car-count {
            background: rgba(255, 102, 0, 0.2);
            color: var(--primary-orange);
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 0.9em;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .line-chart-box, .top-cars-box, .chart-box {
                min-width: 100%;
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
            <h1 class="page-title">Дашборд</h1>
            <p class="page-subtitle">Продвинутая статистика платформы</p>

            <!-- Общая статистика пользователей -->
            <div class="stats-cards-grid">
                <div class="stat-card-new">
                    <div class="stat-value orange"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Всего пользователей</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-value green"><?php echo $activeUsers; ?></div>
                    <div class="stat-label">Активных</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-value red"><?php echo $blockedUsers; ?></div>
                    <div class="stat-label">Заблокированных</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-value gray"><?php echo $deletedUsers; ?></div>
                    <div class="stat-label">Удаленных</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-value orange"><?php echo $onlineUsers; ?></div>
                    <div class="stat-label">Онлайн</div>
                </div>
                <div class="stat-card-new">
                    <div class="stat-value gray"><?php echo $offlineUsers; ?></div>
                    <div class="stat-label">Офлайн</div>
                </div>
            </div>

            <!-- Круговые диаграммы -->
            <div class="charts-row">
                <div class="chart-box">
                    <h3>Избранное</h3>
                    <div class="chart-container">
                        <canvas id="favChart"></canvas>
                    </div>
                </div>
                <div class="chart-box">
                    <h3>Корзина</h3>
                    <div class="chart-container">
                        <canvas id="cartChart"></canvas>
                    </div>
                </div>
                <div class="chart-box">
                    <h3>Запись на просмотр</h3>
                    <div class="chart-container">
                        <canvas id="testDriveChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bottom-row">
                <!-- Динамика регистраций -->
                <div class="line-chart-box">
                    <h3>Динамика регистраций (последние 7 дней)</h3>
                    <div class="line-chart-container">
                        <canvas id="regChart"></canvas>
                    </div>
                </div>

                <!-- Топ 5 авто -->
                <div class="top-cars-box">
                    <h3><i class="fas fa-star"></i> Топ-5 в Избранном</h3>
                    <?php if (empty($topFavorites)): ?>
                        <p style="text-align:center; color: var(--text-gray); margin-top:20px;">Пока нет данных</p>
                    <?php else: ?>
                        <?php foreach ($topFavorites as $fav): ?>
                        <div class="top-car-item">
                            <div class="car-name"><?php echo htmlspecialchars($fav['brand'] . ' ' . $fav['model']); ?></div>
                            <div class="car-count"><?php echo $fav['fav_count']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Инициализация Chart.js -->
    <script>
        // Общие настройки для круговых диаграмм
        const pieOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#ffffff' }
                }
            }
        };

        // Данные из PHP
        const statsData = {
            fav: [<?php echo $usersWithFavorites; ?>, <?php echo $usersWithoutFavorites; ?>],
            cart: [<?php echo $usersWithCart; ?>, <?php echo $usersWithoutCart; ?>],
            testDrive: [<?php echo $usersWithTestDrives; ?>, <?php echo $usersWithoutTestDrives; ?>],
            regDates: <?php echo json_encode($dates); ?>,
            regCounts: <?php echo json_encode(array_values($regCounts)); ?>
        };

        // 1. Избранное
        new Chart(document.getElementById('favChart'), {
            type: 'doughnut',
            data: {
                labels: ['Добавили', 'Не добавляли'],
                datasets: [{
                    data: statsData.fav,
                    backgroundColor: ['#ff6600', '#333333'],
                    borderWidth: 0
                }]
            },
            options: pieOptions
        });

        // 2. Корзина
        new Chart(document.getElementById('cartChart'), {
            type: 'doughnut',
            data: {
                labels: ['Добавили', 'Не добавляли'],
                datasets: [{
                    data: statsData.cart,
                    backgroundColor: ['#4CAF50', '#333333'],
                    borderWidth: 0
                }]
            },
            options: pieOptions
        });

        // 3. Запись на просмотр
        new Chart(document.getElementById('testDriveChart'), {
            type: 'doughnut',
            data: {
                labels: ['Записались', 'Не записывались'],
                datasets: [{
                    data: statsData.testDrive,
                    backgroundColor: ['#2196F3', '#333333'],
                    borderWidth: 0
                }]
            },
            options: pieOptions
        });

        // 4. Линейный график регистраций
        new Chart(document.getElementById('regChart'), {
            type: 'line',
            data: {
                labels: statsData.regDates,
                datasets: [{
                    label: 'Новых пользователей',
                    data: statsData.regCounts,
                    borderColor: '#ff6600',
                    backgroundColor: 'rgba(255, 102, 0, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        ticks: { color: '#b0b0b0' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    y: {
                        ticks: { color: '#b0b0b0', stepSize: 1 },
                        grid: { color: 'rgba(255,255,255,0.1)' },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
