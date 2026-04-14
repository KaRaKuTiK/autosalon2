<?php
require_once 'config.php';
$admin = checkAdminAuth();

$action = $_GET['action'] ?? 'list';
$editSalon = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $working_hours = trim($_POST['working_hours'] ?? '');

    if (isset($_POST['add'])) {
        if (!empty($name) && !empty($address)) {
            $stmt = $pdo->prepare("INSERT INTO salons (name, address, phone, email, working_hours) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $address, $phone, $email, $working_hours]);
            $_SESSION['success'] = "Салон успешно добавлен.";
        } else {
            $_SESSION['error'] = "Название и Адрес обязательны.";
        }
        header("Location: salons.php");
        exit();
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        if (!empty($name) && !empty($address)) {
            $stmt = $pdo->prepare("UPDATE salons SET name=?, address=?, phone=?, email=?, working_hours=? WHERE id=?");
            $stmt->execute([$name, $address, $phone, $email, $working_hours, $id]);
            $_SESSION['success'] = "Салон обновлен.";
        } else {
            $_SESSION['error'] = "Название и Адрес обязательны.";
        }
        header("Location: salons.php");
        exit();
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Проверка, есть ли автомобили в инвентаре этого салона
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE salon_id = ?");
    $checkStmt->execute([$id]);
    if ($checkStmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Нельзя удалить салон, в котором есть автомобили (проверьте инвентарь).";
    } else {
        $stmt = $pdo->prepare("DELETE FROM salons WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Салон удален.";
    }
    header("Location: salons.php");
    exit();
}

if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM salons WHERE id = ?");
    $stmt->execute([$id]);
    $editSalon = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получаем список салонов
$stmt = $pdo->query("SELECT * FROM salons ORDER BY id DESC");
$salons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление салонами - Admin Panel</title>
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
        
        .content-layout { display: flex; gap: 30px; flex-wrap: wrap; }
        .table-section { flex: 2; min-width: 60%; background: rgba(26, 26, 26, 0.4); padding: 20px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.05); }
        .form-section { flex: 1; min-width: 300px; background: rgba(26, 26, 26, 0.4); padding: 20px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.05); align-self: flex-start; }

        .section-title { color: var(--primary-orange); margin-bottom: 20px; font-size: 1.3em; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: var(--text-gray); }
        .form-control { width: 100%; padding: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 5px; color: #fff; }
        .form-control:focus { outline: none; border-color: var(--primary-orange); }
        
        .btn { padding: 10px 20px; background: var(--primary-orange); color: #fff; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: var(--hover-orange); }
        .btn-secondary { background: #555; }
        .btn-secondary:hover { background: #666; }
        .btn-danger { background: rgba(220, 53, 69, 0.8); }
        .btn-danger:hover { background: rgba(220, 53, 69, 1); }

        table { width: 100%; border-collapse: collapse; }
        th { background: rgba(255, 102, 0, 0.1); color: var(--primary-orange); padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .actions { display: flex; gap: 8px; }
        .actions a { padding: 6px 10px; border-radius: 4px; color: #fff; text-decoration: none; }
        .actions .edit { background: rgba(0, 123, 255, 0.8); }
        .actions .delete { background: rgba(220, 53, 69, 0.8); }
        
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
                <div style="color: var(--text-gray);">
                    <i class="fas fa-user-shield"></i> Администратор: <strong style="color: #fff;"><?php echo htmlspecialchars($admin['login'] ?? 'admin'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin_nav.php'; ?>

    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Управление салонами</h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="content-layout">
                <div class="table-section">
                    <div class="section-title"><i class="fas fa-building"></i> Список салонов</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Адрес</th>
                                <th>Телефон</th>
                                <th>Email</th>
                                <th>Часы работы</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($salons)): ?>
                            <tr><td colspan="6" style="text-align: center;">Салоны не найдены.</td></tr>
                            <?php else: ?>
                                <?php foreach ($salons as $salon): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($salon['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($salon['address']); ?></td>
                                    <td><?php echo htmlspecialchars($salon['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($salon['email']); ?></td>
                                    <td><?php echo htmlspecialchars($salon['working_hours']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?php echo $salon['id']; ?>" class="edit" title="Редактировать"><i class="fas fa-edit"></i></a>
                                            <a href="?action=delete&id=<?php echo $salon['id']; ?>" class="delete" title="Удалить" onclick="return confirm('Точно удалить этот салон?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-plus-circle"></i> <?php echo $editSalon ? 'Редактировать салон' : 'Добавить салон'; ?>
                    </div>
                    <form method="POST" action="salons.php">
                        <?php if ($editSalon): ?>
                            <input type="hidden" name="id" value="<?php echo $editSalon['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Название салона <span style="color:red">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo $editSalon ? htmlspecialchars($editSalon['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Адрес <span style="color:red">*</span></label>
                            <input type="text" name="address" class="form-control" value="<?php echo $editSalon ? htmlspecialchars($editSalon['address']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $editSalon ? htmlspecialchars($editSalon['phone']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $editSalon ? htmlspecialchars($editSalon['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Часы работы</label>
                            <input type="text" name="working_hours" class="form-control" placeholder="Например: Пн-Вс: 09:00 - 20:00" value="<?php echo $editSalon ? htmlspecialchars($editSalon['working_hours']) : ''; ?>">
                        </div>
                        
                        <?php if ($editSalon): ?>
                            <button type="submit" name="edit" class="btn" style="width: 100%; margin-bottom: 10px;">Сохранить изменения</button>
                            <a href="salons.php" class="btn btn-secondary" style="width: 100%; text-align: center;">Отменить</a>
                        <?php else: ?>
                            <button type="submit" name="add" class="btn" style="width: 100%;"><i class="fas fa-plus"></i> Добавить</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
