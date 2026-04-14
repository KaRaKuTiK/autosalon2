<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Обработка изменения статуса заявки
if (isset($_GET['update_status'])) {
    $requestId = (int)$_GET['update_status'];
    $newStatus = $_GET['status'] ?? '';
    
    if (in_array($newStatus, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE test_drive_requests SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $requestId]);
            $_SESSION['success'] = 'Статус заявки обновлён';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Ошибка при обновлении статуса';
        }
    }
    
    header("Location: requests_list.php");
    exit();
}

// Получаем список заявок
try {
    $stmt = $pdo->query("
        SELECT tr.*, 
               u.full_name as user_name, u.email as user_email,
               c.brand, c.model, c.year
        FROM test_drive_requests tr
        LEFT JOIN users u ON tr.user_id = u.id
        LEFT JOIN cars c ON tr.car_id = c.id
        ORDER BY tr.created_at DESC
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $requests = [];
    $_SESSION['error'] = 'Ошибка при загрузке заявок';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки на просмотр - Админ-панель</title>
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

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .main-content {
            padding: 40px 0;
        }

        .page-title {
            font-size: 2.5em;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .success-message, .error-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .error-message {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .requests-grid {
            display: grid;
            gap: 20px;
        }

        .request-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 102, 0, 0.4);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .request-info h3 {
            color: var(--primary-orange);
            font-size: 1.3em;
            margin-bottom: 5px;
        }

        .request-date {
            color: var(--text-gray);
            font-size: 0.9em;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd43b;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .status-confirmed {
            background: rgba(0, 123, 255, 0.2);
            color: #4dabf7;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }

        .status-completed {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-gray);
        }

        .detail-item i {
            color: var(--primary-orange);
            width: 20px;
        }

        .detail-item strong {
            color: var(--text-light);
        }

        .request-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 0.9em;
        }

        .btn-confirm {
            background: rgba(0, 123, 255, 0.2);
            color: #4dabf7;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }

        .btn-complete {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .btn-cancel {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 4em;
            color: var(--primary-orange);
            opacity: 0.5;
            margin-bottom: 20px;
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
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Назад
                </a>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Заявки на просмотр автомобилей</h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="requests-grid">
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Заявок пока нет</h3>
                        <p>Здесь будут отображаться заявки клиентов на просмотр автомобилей</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div class="request-info">
                                    <h3><?php echo htmlspecialchars($request['full_name']); ?></h3>
                                    <div class="request-date">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('d.m.Y H:i', strtotime($request['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="status-badge status-<?php echo $request['status']; ?>">
                                    <?php 
                                    $statuses = [
                                        'pending' => 'Ожидает',
                                        'confirmed' => 'Подтверждена',
                                        'completed' => 'Завершена',
                                        'cancelled' => 'Отменена'
                                    ];
                                    echo $statuses[$request['status']];
                                    ?>
                                </div>
                            </div>

                            <div class="request-details">
                                <div class="detail-item">
                                    <i class="fas fa-car"></i>
                                    <span><strong><?php echo htmlspecialchars($request['brand'] . ' ' . $request['model']); ?></strong> (<?php echo $request['year']; ?>)</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($request['phone']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($request['salon_address']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d.m.Y', strtotime($request['viewing_date'])); ?> в <?php echo date('H:i', strtotime($request['viewing_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-credit-card"></i>
                                    <span>
                                        <?php 
                                        $payments = ['cash' => 'Наличные', 'card' => 'Картой', 'crypto' => 'Криптовалюта'];
                                        echo $payments[$request['payment_method']];
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo htmlspecialchars($request['user_email']); ?></span>
                                </div>
                            </div>

                            <?php if ($request['preferences']): ?>
                                <div style="margin-bottom: 15px; padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                                    <strong><i class="fas fa-comment"></i> Пожелания:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($request['preferences'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="request-actions">
                                <?php if ($request['status'] === 'pending'): ?>
                                    <a href="?update_status=<?php echo $request['id']; ?>&status=confirmed" class="btn btn-confirm btn-small">
                                        <i class="fas fa-check"></i> Подтвердить
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($request['status'] === 'confirmed'): ?>
                                    <a href="?update_status=<?php echo $request['id']; ?>&status=completed" class="btn btn-complete btn-small">
                                        <i class="fas fa-check-double"></i> Завершить
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (in_array($request['status'], ['pending', 'confirmed'])): ?>
                                    <a href="?update_status=<?php echo $request['id']; ?>&status=cancelled" 
                                       class="btn btn-cancel btn-small"
                                       onclick="return confirm('Отменить эту заявку?')">
                                        <i class="fas fa-times"></i> Отменить
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
