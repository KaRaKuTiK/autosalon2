<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$user = $_SESSION['user'];
$user_id = $user['id'];

// Обработка удаления автомобиля из корзины (Проблема 2)
if (isset($_POST['delete_item_id'])) {
    $delete_cart_id = (int)$_POST['delete_item_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$delete_cart_id, $user_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Автомобиль успешно удален из корзины!";
        } else {
            $_SESSION['error'] = "Не удалось удалить автомобиль (возможно, он уже удален).";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка при удалении: " . $e->getMessage();
    }
    header("Location: cart.php");
    exit();
}

// Получаем товары в корзине пользователя
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cr.id as cart_id, cr.quantity, cr.added_at, 
               (c.price * cr.quantity) as total_price,
               (SELECT image_path FROM car_images WHERE car_id = c.id AND is_main = 1 LIMIT 1) as main_image
        FROM cart cr 
        JOIN cars c ON cr.car_id = c.id 
        WHERE cr.user_id = ? 
        ORDER BY cr.added_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Вычисляем общую сумму
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['total_price'];
    }
} catch (PDOException $e) {
    $cart_items = [];
    $total_amount = 0;
    $_SESSION['error'] = "Ошибка при загрузке корзины: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - AutoSalon Premium</title>
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
            --danger-red: #dc3545;
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

        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (max-width: 968px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
        }

        .cart-items {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .cart-summary {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            height: fit-content;
            position: sticky;
            top: 150px;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cart-header h2 {
            color: var(--text-light);
            font-size: 1.8em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-header h2 i {
            color: var(--primary-orange);
        }

        .cart-count {
            color: var(--text-gray);
            font-size: 1.1em;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-gray);
        }

        .empty-cart i {
            font-size: 4em;
            color: var(--primary-orange);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-cart h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .empty-cart p {
            margin-bottom: 25px;
            font-size: 1.1em;
        }

        .btn {
            padding: 12px 30px;
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
            background: var(--primary-orange);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background: var(--hover-orange);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 25px;
            background: rgba(40, 40, 40, 0.5);
            border-radius: 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            border-color: rgba(255, 102, 0, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .car-image {
            width: 120px;
            height: 100px;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 2.5em;
            flex-shrink: 0;
        }

        .car-details {
            flex: 1;
        }

        .car-title {
            color: var(--text-light);
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .car-specs {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .car-spec {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-gray);
            font-size: 0.9em;
        }

        .car-spec i {
            color: var(--primary-orange);
            font-size: 0.8em;
        }

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

        .cart-controls {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 15px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 5px;
        }

        .quantity-btn {
            background: transparent;
            border: none;
            color: var(--text-light);
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: rgba(255, 102, 0, 0.2);
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            background: transparent;
            border: none;
            color: var(--text-light);
            font-size: 1em;
            font-weight: 600;
        }

        .quantity-input:focus {
            outline: none;
        }

        .item-price {
            text-align: right;
        }

        .price {
            color: var(--primary-orange);
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .total-price {
            color: var(--text-gray);
            font-size: 0.9em;
        }

        .remove-btn {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9em;
        }

        .remove-btn:hover {
            background: rgba(220, 53, 69, 0.3);
            transform: translateY(-1px);
        }

        .summary-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-header h3 {
            color: var(--text-light);
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .summary-header h3 i {
            color: var(--primary-orange);
        }

        .summary-details {
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--text-gray);
        }

        .summary-value {
            color: var(--text-light);
            font-weight: 600;
        }

        .summary-total {
            font-size: 1.2em;
            color: var(--primary-orange);
            font-weight: 700;
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-orange);
            color: var(--text-light);
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            background: var(--hover-orange);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
        }

        .checkout-btn:disabled {
            background: var(--text-gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .cart-item {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .cart-controls {
                align-items: center;
                flex-direction: row;
                justify-content: space-between;
            }
            
            .car-image {
                align-self: center;
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
            .cart-controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .quantity-controls {
                order: 2;
            }
            
            .item-price {
                order: 1;
                text-align: center;
            }
            
            .remove-btn {
                order: 3;
                width: 100%;
                justify-content: center;
            }
        }

        /* Модальное окно */
        .modal {
            display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.8); backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: #1a1a1a; margin: 5% auto; padding: 30px;
            border: 1px solid #ff6600; border-radius: 15px;
            width: 90%; max-width: 500px;
            color: #fff;
        }
        .close-modal {
            color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;
        }
        .close-modal:hover { color: #ff6600; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #b0b0b0; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border-radius: 5px; background: rgba(0,0,0,0.5);
            border: 1px solid #333; color: #fff;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #ff6600;
        }
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
                <li><a href="favorites.php"><i class="fas fa-heart"></i> Избранное</a></li>
                <li><a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Корзина</a></li>
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
                <h1 class="page-title">Корзина покупок</h1>
                <p class="page-subtitle">Управляйте выбранными автомобилями</p>
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

            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header">
                        <h2><i class="fas fa-shopping-cart"></i> Ваши автомобили</h2>
                        <div class="cart-count"><?php echo count($cart_items); ?> автомобилей</div>
                    </div>

                    <?php if (empty($cart_items)): ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Ваша корзина пуста</h3>
                            <p>Добавьте автомобили из каталога, чтобы они появились здесь</p>
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-car"></i> Перейти в каталог
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="car-image">
                                <?php if (!empty($item['main_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['main_image']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                <?php else: ?>
                                    <?php 
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
                                    echo $carImages[$item['brand']] ?? '🚗';
                                    ?>
                                <?php endif; ?>
                            </div>
                            <div class="car-details">
                                <h3 class="car-title"><?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?></h3>
                                <div class="car-specs">
                                    <div class="car-spec">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo htmlspecialchars($item['year']); ?> год</span>
                                    </div>
                                    <div class="car-spec">
                                        <i class="fas fa-palette"></i>
                                        <span><?php echo htmlspecialchars($item['color']); ?></span>
                                    </div>
                                    <span class="car-type"><?php echo htmlspecialchars($item['type']); ?></span>
                                </div>
                                <p class="car-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            </div>
                            <div class="cart-controls">
                                <form action="update_cart.php" method="POST" class="quantity-controls">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" name="decrease" class="quantity-btn" onclick="this.form.quantity.value=parseInt(this.form.quantity.value)-1">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" onchange="this.form.submit()">
                                    <button type="submit" name="increase" class="quantity-btn" onclick="this.form.quantity.value=parseInt(this.form.quantity.value)+1">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                                <div class="item-price">
                                    <div class="price"><?php echo number_format($item['price'], 0, ',', ' '); ?> ₽</div>
                                    <div class="total-price">Итого: <?php echo number_format($item['total_price'], 0, ',', ' '); ?> ₽</div>
                                </div>
                                <form action="cart.php" method="POST" style="margin: 0; padding: 0;">
                                    <input type="hidden" name="delete_item_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" class="remove-btn">
                                        <i class="fas fa-trash"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($cart_items)): ?>
                <div class="cart-summary">
                    <div class="summary-header">
                        <h3><i class="fas fa-receipt"></i> Итог заказа</h3>
                    </div>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span class="summary-label">Количество автомобилей:</span>
                            <span class="summary-value"><?php echo count($cart_items); ?> шт.</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Общая стоимость:</span>
                            <span class="summary-value summary-total"><?php echo number_format($total_amount, 0, ',', ' '); ?> ₽</span>
                        </div>
                    </div>
                    <button type="button" class="checkout-btn" id="openTestDriveModal">
                        <i class="fas fa-calendar-check"></i> Записаться на просмотр
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно записи на просмотр -->
    <div id="testDriveModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 style="margin-bottom: 20px; color: #ff6600;">Запись на просмотр</h2>
            <div id="modalResponse" style="display:none; font-weight:bold; margin-bottom: 15px;"></div>
            <form id="testDriveForm">
                <div class="form-group">
                    <label>ФИО *</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Адрес салона *</label>
                    <select name="salon_address" required>
                        <option value="">Выберите салон</option>
                        <option value="г. Москва, ул. Ленина, 1">г. Москва, ул. Ленина, 1</option>
                        <option value="г. Санкт-Петербург, Невский пр. 50">г. Санкт-Петербург, Невский пр. 50</option>
                        <option value="г. Казань, ул. Баумана, 10">г. Казань, ул. Баумана, 10</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Номер телефона *</label>
                    <input type="tel" name="phone" id="phoneInput" placeholder="+7 (___) ___-__-__" required>
                </div>
                <div class="form-group">
                    <label>Дата встречи *</label>
                    <input type="date" name="view_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Время показа *</label>
                    <select name="view_time" required>
                        <option value="">Выберите время</option>
                        <?php
                            for ($h=10; $h<=19; $h++) {
                                foreach (['00', '30'] as $m) {
                                    echo "<option value=\"$h:$m\">$h:$m</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Предпочитаемый способ оплаты *</label>
                    <select name="payment_method" required>
                        <option value="Наличные">Наличные</option>
                        <option value="Картой">Картой</option>
                        <option value="Криптовалюта">Криптовалюта</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Пожелание к показу авто</label>
                    <textarea name="wishes" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Отправить заявку</button>
            </form>
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
        // Автоматическая отправка формы при изменении количества
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Подтверждение удаления - удалено во избежание двойного confirm
        // (уже есть onclick в самом элементе)

        // Модальное окно и логика
        const modal = document.getElementById('testDriveModal');
        const openBtn = document.getElementById('openTestDriveModal');
        const closeBtn = document.querySelector('.close-modal');
        const phoneInput = document.getElementById('phoneInput');
        const testDriveForm = document.getElementById('testDriveForm');
        const modalResponse = document.getElementById('modalResponse');

        if (openBtn) {
            openBtn.onclick = function() {
                modal.style.display = "block";
            }
        }

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Простая маска для телефона +7XXXXXXXXXX
        phoneInput.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
            if (!x[1]) {
                e.target.value = '+7';
                return;
            }
            e.target.value = '+7' + (x[2] ? ' (' + x[2] : '') + (x[3] ? ') ' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
        });

        // Отправка формы AJAX
        testDriveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(testDriveForm);
            
            fetch('submit_viewing.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    modalResponse.style.display = 'block';
                    modalResponse.style.color = '#51cf66';
                    modalResponse.innerText = data.message;
                    testDriveForm.reset();
                    setTimeout(() => {
                        modal.style.display = "none";
                        modalResponse.style.display = 'none';
                    }, 3000);
                } else {
                    modalResponse.style.display = 'block';
                    modalResponse.style.color = '#ff6b6b';
                    modalResponse.innerText = data.message || 'Произошла ошибка';
                }
            })
            .catch(err => {
                modalResponse.style.display = 'block';
                modalResponse.style.color = '#ff6b6b';
                modalResponse.innerText = 'Ошибка сети';
            });
        });
    </script>

    <?php include 'chat_widget.php'; ?>
</body>
</html>