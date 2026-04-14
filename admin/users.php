<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Обработка действий (Блокировка / Удаление)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    if ($_GET['action'] == 'toggle_block') {
        // Убедимся, что есть колонка is_blocked (ошибка не упадет жестко, если ее нет, но PDOException отловим)
        try {
            // Переключаем статус блокировки, защищая админов от блокировки
            $stmt = $pdo->prepare("UPDATE users SET is_blocked = NOT is_blocked WHERE id = ? AND (role != 'admin' OR role IS NULL)");
            $stmt->execute([$userId]);
            $_SESSION['success'] = "Статус блокировки пользователя обновлен.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка блокировки: " . $e->getMessage();
        }
        header("Location: users.php");
        exit();
    }
    
    if ($_GET['action'] == 'delete') {
        try {
            // Проверка на наличие активных заявок
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM test_drive_requests WHERE user_id = ? AND status IN ('pending', 'confirmed')");
            $checkStmt->execute([$userId]);
            $activeRequests = $checkStmt->fetchColumn();
            
            if ($activeRequests > 0) {
                $_SESSION['error'] = "Невозможно удалить пользователя. У него есть активные или подтвержденные заявки.";
            } else {
                $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND (role != 'admin' OR role IS NULL)");
                if ($delStmt->execute([$userId])) {
                    $_SESSION['success'] = "Пользователь успешно удален.";
                } else {
                    $_SESSION['error'] = "Не удалось удалить пользователя.";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка: столбец user_id может не существовать в таблице test_drive_requests.";
            // Резервное удаление если user_id в test_drive_requests нет
            $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND (role != 'admin' OR role IS NULL)");
            $delStmt->execute([$userId]);
            $_SESSION['success'] = "Пользователь удален (без проверки заявок).";
        }
        header("Location: users.php");
        exit();
    }
}

// Загрузка пользователей
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM users WHERE (role != 'admin' OR role IS NULL)";
$params = [];

if (!empty($search)) {
    $query .= " AND (login LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY registration_date DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $_SESSION['error'] = "Ошибка при загрузке пользователей: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - Admin Panel</title>
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

        .main-content {
            padding: 40px 0;
        }

        .page-title {
            font-size: 2.5em;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--primary-orange) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .table-container {
            background: rgba(26, 26, 26, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-orange);
            font-size: 1.2em;
            font-weight: 600;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-light);
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary-orange);
        }

        .btn {
            padding: 8px 15px;
            background: var(--primary-orange);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            font-size: 0.9em;
        }

        .btn:hover {
            background: var(--hover-orange);
        }

        .btn-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 4px;
            padding: 6px 10px;
        }

        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.4);
        }

        .btn-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd43b;
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 4px;
            padding: 6px 10px;
        }
        .btn-warning:hover { background: rgba(255, 193, 7, 0.4); }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: rgba(255, 102, 0, 0.1);
            color: var(--primary-orange);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85em;
            padding: 15px;
            border-bottom: 2px solid rgba(255, 102, 0, 0.3);
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95em;
            vertical-align: middle;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .actions-group {
            display: flex;
            gap: 10px;
        }
        
        .blocked-badge {
            background: #ff6b6b;
            color: #111;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 10px;
        }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(40, 167, 69, 0.2); color: #51cf66; border: 1px solid rgba(40, 167, 69, 0.3); }
        .alert-error { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid rgba(220, 53, 69, 0.3); }
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
                    <span style="color: var(--text-gray);"><i class="fas fa-user-shield"></i> Вы вошли как администратор: <strong style="color: #fff;"><?php echo htmlspecialchars($admin['login'] ?? 'admin'); ?></strong></span>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Управление пользователями</h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-users"></i> Список пользователей
                    </div>
                    <form class="search-form" method="GET" action="users.php">
                        <input type="text" name="search" class="search-input" placeholder="Поиск по логину/email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn"><i class="fas fa-search"></i> Искать</button>
                        <?php if(!empty($search)): ?>
                            <a href="users.php" class="btn" style="background: #555;">Сбросить</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Email</th>
                            <th>ФИО</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="6" style="text-align: center;">Пользователи не найдены.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($u['login']); ?>
                                    <?php if(isset($u['is_blocked']) && $u['is_blocked']): ?>
                                        <span class="blocked-badge">Заблокирован</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($u['registration_date'])); ?></td>
                                <td>
                                    <div class="actions-group">
                                        <?php if(isset($u['is_blocked']) && $u['is_blocked']): ?>
                                            <a href="?action=toggle_block&id=<?php echo $u['id']; ?>" class="btn-warning" title="Разблокировать"><i class="fas fa-unlock"></i></a>
                                        <?php else: ?>
                                            <a href="?action=toggle_block&id=<?php echo $u['id']; ?>" class="btn-warning" title="Заблокировать"><i class="fas fa-lock"></i></a>
                                        <?php endif; ?>
                                        
                                        <a href="?action=delete&id=<?php echo $u['id']; ?>" class="btn-danger" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
