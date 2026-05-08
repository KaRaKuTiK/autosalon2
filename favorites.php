<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'] ?? $_SESSION['admin'];
$user_id = $user['id'];

// Получаем избранные авто с JOIN к cars
$sql = "SELECT cars.*, (SELECT image_path FROM car_images WHERE car_id = cars.id AND is_main = 1 LIMIT 1) as main_image 
        FROM favorites f 
        JOIN cars ON f.car_id = cars.id 
        WHERE f.user_id = ? 
        ORDER BY f.added_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Для отображения кнопки в избранном нам нужен массив id
$favorites = array_column($cars, 'id');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное - AutoSalon Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-black: #0a0a0a;
            --dark-black: #111111;
            --light-black: #1a1a1a;
            --primary-orange: #ff6600;
            --hover-orange: #ff8533;
            --text-light: #ffffff;
            --text-gray: #b0b0b0;
            --card-bg: rgba(26, 26, 26, 0.8);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-black) 50%, #1a0f00 100%); color: var(--text-light); min-height: 100vh; position: relative; overflow-x: hidden; }
        .header { background: rgba(10, 10, 10, 0.95); padding: 20px 0; border-bottom: 2px solid var(--primary-orange); position: sticky; top: 0; z-index: 1000; }
        .container { width: 90%; max-width: 1400px; margin: 0 auto; }
        .logo { display: flex; align-items: center; justify-content: center; gap: 15px; }
        .logo-icon { font-size: 2.5em; color: var(--primary-orange); }
        .logo-text { color: var(--primary-orange); font-size: 2.5em; font-weight: 700; }
        .logo-text span { color: var(--text-light); font-weight: 300; }
        .nav-menu { background: rgba(26, 26, 26, 0.95); padding: 15px 0; border-bottom: 1px solid rgba(255, 102, 0, 0.2); position: sticky; top: 82px; z-index: 999; }
        .nav-links { display: flex; justify-content: center; gap: 10px; list-style: none; flex-wrap: wrap; }
        .nav-links a { color: var(--text-light); text-decoration: none; padding: 12px 25px; border-radius: 25px; transition: all 0.3s; display: flex; align-items: center; gap: 8px; border: 1px solid transparent; }
        .nav-links a:hover { background: rgba(255, 102, 0, 0.1); color: var(--primary-orange); }
        .nav-links a.active { background: var(--primary-orange); color: var(--text-light); }
        .main-content { padding: 40px 0; }
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-title { font-size: 3em; font-weight: 700; color: var(--primary-orange); }
        .page-subtitle { color: var(--text-gray); font-size: 1.2em; }
        .cars-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
        .car-card { background: var(--card-bg); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255, 102, 0, 0.2); transition: all 0.3s; position: relative; }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(255, 102, 0, 0.2); }
        .car-image { width: 100%; height: 220px; background: #333; display: flex; align-items: center; justify-content: center; font-size: 4em; position: relative; }
        .car-info { padding: 25px; }
        .car-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .car-title { font-size: 1.4em; font-weight: 600; }
        .car-price { color: var(--primary-orange); font-size: 1.6em; font-weight: 700; }
        .car-details { margin-bottom: 20px; }
        .car-detail { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; color: var(--text-gray); font-size: 0.95em; }
        .car-detail i { color: var(--primary-orange); width: 16px; }
        .car-description { color: var(--text-gray); font-size: 0.9em; line-height: 1.5; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .car-actions { display: flex; gap: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: var(--primary-orange); color: var(--text-light); }
        .btn-primary:hover { background: var(--hover-orange); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .empty-state { text-align: center; padding: 50px; font-size: 1.2em; color: var(--text-gray); }
        .empty-state i { font-size: 3em; color: var(--primary-orange); margin-bottom: 15px; display: block; }
    </style>
</head>
<body>
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
                <li><a href="favorites.php" class="active"><i class="fas fa-heart"></i> Избранное</a></li>
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
            <div class="page-header">
                <h1 class="page-title">Моё избранное</h1>
                <p class="page-subtitle">Автомобили, которые вам понравились</p>
            </div>

            <?php if (empty($cars)): ?>
                <div class="empty-state">
                    <i class="fas fa-heart-broken"></i>
                    У вас пока нет избранных автомобилей.<br>
                    <a href="dashboard.php" style="color: var(--primary-orange); text-decoration: none; margin-top: 15px; display: inline-block;">Перейти в каталог</a>
                </div>
            <?php else: ?>
                <div class="cars-grid">
                    <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <?php 
                            if (!empty($car['main_image'])) {
                                echo '<img src="' . htmlspecialchars($car['main_image']) . '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top:0; left:0; z-index: 1;">';
                            } else {
                                echo '🚗';
                            }
                            ?>
                        </div>
                        <div class="car-info">
                            <div class="car-header">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                                <div class="car-price"><?php echo number_format($car['price'], 0, ',', ' '); ?> ₽</div>
                            </div>
                            <div class="car-details">
                                <div class="car-detail"><i class="fas fa-calendar-alt"></i> <span>Год выпуска: <strong><?php echo htmlspecialchars($car['year']); ?></strong></span></div>
                                <div class="car-detail"><i class="fas fa-palette"></i> <span>Цвет: <strong><?php echo htmlspecialchars($car['color']); ?></strong></span></div>
                                <div class="car-detail"><i class="fas fa-tag"></i> <span>Тип: <strong><?php echo htmlspecialchars($car['type']); ?></strong></span></div>
                            </div>
                            <p class="car-description"><?php echo htmlspecialchars($car['description']); ?></p>
                            <div class="car-actions">
                                <form action="add_to_cart.php" method="POST" style="display: inline; flex: 1;">
                                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-shopping-cart"></i> В корзину
                                    </button>
                                </form>
                                <a href="toggle_favorite.php?car_id=<?php echo $car['id']; ?>" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Удалить
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
