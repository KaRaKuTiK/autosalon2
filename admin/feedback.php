<?php
require_once 'config.php';
$admin = checkAdminAuth();

// Изменение статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['feedback_id'];
    $status = $_POST['status'];
    
    if (in_array($status, ['new', 'in_progress', 'resolved'])) {
        $stmt = $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['success'] = "Статус обращения обновлен.";
    }
    header("Location: feedback.php");
    exit();
}

// Удаление сообщения
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Обращение удалено.";
    header("Location: feedback.php");
    exit();
}

// Получение списка сообщений
$stmt = $pdo->query("
    SELECT f.*, u.login, u.email 
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    ORDER BY CASE f.status WHEN 'new' THEN 1 WHEN 'in_progress' THEN 2 ELSE 3 END, f.created_at DESC
");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обратная связь - Admin Panel</title>
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-black) 50%, #1a0f00 100%); color: var(--text-light); min-height: 100vh; }
        .header { background: rgba(10, 10, 10, 0.95); backdrop-filter: blur(10px); padding: 20px 0; border-bottom: 2px solid var(--primary-orange); box-shadow: 0 4px 20px rgba(255, 102, 0, 0.2); }
        .container { width: 90%; max-width: 1400px; margin: 0 auto; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-icon { font-size: 2em; color: var(--primary-orange); }
        .logo-text { color: var(--primary-orange); font-size: 1.8em; font-weight: 700; }
        
        .main-content { padding: 40px 0; }
        .page-title { font-size: 2.5em; margin-bottom: 30px; text-align: center; color: var(--primary-orange); }

        .table-container { background: rgba(26, 26, 26, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 10px; padding: 20px; overflow-x: auto; }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .table-title { display: flex; align-items: center; gap: 10px; color: var(--primary-orange); font-size: 1.2em; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; }
        th { background: rgba(255, 102, 0, 0.1); color: var(--primary-orange); padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .status-select {
            background: #222;
            color: #fff;
            border: 1px solid #444;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .btn-update { background: var(--primary-orange); border: none; color: white; padding: 6px 12px; cursor: pointer; border-radius: 4px; margin-left: 5px; }
        .btn-update:hover { background: var(--hover-orange); }

        .btn-danger { background: rgba(220, 53, 69, 0.8); color: white; border: none; padding: 6px 12px; border-radius: 4px; text-decoration: none; }
        .btn-danger:hover { background: rgba(220, 53, 69, 1); }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: rgba(40, 167, 69, 0.2); color: #51cf66; border: 1px solid rgba(40, 167, 69, 0.3); }
        .alert-error { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid rgba(220, 53, 69, 0.3); }
        
        .message-cell { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .message-cell:hover { white-space: normal; background: rgba(0,0,0,0.8); position: relative; z-index: 10; border-radius: 4px; padding: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
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
                <div style="color: var(--text-gray);">
                    <i class="fas fa-user-shield"></i> Администратор: <strong style="color: #fff;"><?php echo htmlspecialchars($admin['login'] ?? 'admin'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Обратная связь</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">
                        <i class="fas fa-comments"></i> Обращения пользователей
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Тема</th>
                            <th>Сообщение</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedbacks)): ?>
                        <tr><td colspan="7" style="text-align: center;">Нет новых обращений</td></tr>
                        <?php else: ?>
                            <?php foreach ($feedbacks as $f): ?>
                            <tr>
                                <td><?php echo $f['id']; ?></td>
                                <td><?php echo htmlspecialchars($f['login'] . ' (' . $f['email'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($f['subject']); ?></td>
                                <td class="message-cell" title="Клик/наведение для подробностей"><?php echo htmlspecialchars($f['message']); ?></td>
                                <td>
                                    <form method="POST" style="display: flex;">
                                        <input type="hidden" name="feedback_id" value="<?php echo $f['id']; ?>">
                                        <select name="status" class="status-select">
                                            <option value="new" <?php echo $f['status'] == 'new' ? 'selected' : ''; ?>>Новое</option>
                                            <option value="in_progress" <?php echo $f['status'] == 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                                            <option value="resolved" <?php echo $f['status'] == 'resolved' ? 'selected' : ''; ?>>Решено</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-update"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($f['created_at'])); ?></td>
                                <td>
                                    <a href="?action=delete&id=<?php echo $f['id']; ?>" class="btn-danger" onclick="return confirm('Удалить обращение?')"><i class="fas fa-trash"></i></a>
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
