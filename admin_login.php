<?php
session_start();
// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'config.php';

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin_cars.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход для администраторов - AutoSalon</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Те же стили, что и на главной */
        :root {
            --primary-black: #0a0a0a;
            --dark-black: #111111;
            --primary-orange: #ff6600;
            --hover-orange: #ff8533;
            --text-light: #ffffff;
            --text-gray: #b0b0b0;
            --border-dark: #333333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }

        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-black) 50%, #1a0f00 100%);
            color: var(--text-light); min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }

        .auth-form {
            background: rgba(26, 26, 26, 0.95);
            padding: 50px 40px; border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.4);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 102, 0, 0.2);
            width: 100%; max-width: 420px;
            position: relative; overflow: hidden;
            text-align: center;
        }
        .auth-form::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #dc3545, var(--primary-orange));
        }

        .auth-form h2 { color: var(--text-light); font-size: 2em; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 25px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-light); font-weight: 500; }
        
        .input-with-icon { position: relative; }
        .input-with-icon i {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-gray);
        }
        .input-with-icon input {
            width: 100%; padding: 15px 15px 15px 45px; background: rgba(40, 40, 40, 0.8);
            border: 1px solid var(--border-dark); border-radius: 10px; color: var(--text-light); font-size: 1em;
        }
        .input-with-icon input:focus {
            outline: none; border-color: var(--primary-orange);
        }

        .btn {
            width: 100%; padding: 16px; background: linear-gradient(135deg, #dc3545, var(--primary-orange));
            color: var(--text-light); border: none; border-radius: 10px; font-size: 1.1em; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }

        .error-message {
            background: rgba(220, 53, 69, 0.2); color: #ff6b6b; padding: 15px; border-radius: 10px; margin-bottom: 25px;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        .back-link { margin-top: 20px; display: inline-block; color: var(--text-gray); text-decoration: none; transition: 0.3s; }
        .back-link:hover { color: var(--text-light); }
    </style>
</head>
<body>

<div class="auth-form">
    <h2><i class="fas fa-user-shield" style="color: var(--primary-orange); margin-right: 10px;"></i>Панель Администратора</h2>
    
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form action="admin_login_process.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label for="login">Имя пользователя</label>
            <div class="input-with-icon">
                <input type="text" id="login" name="login" required placeholder="Введите логин администратора">
                <i class="fas fa-user-lock"></i>
            </div>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль</label>
            <div class="input-with-icon">
                <input type="password" id="password" name="password" required placeholder="Введите пароль">
                <i class="fas fa-key"></i>
            </div>
        </div>
        
        <button type="submit" class="btn">Войти в админ-панель</button>
    </form>

    <a href="index.php" class="back-link">&larr; Вернуться на главный сайт</a>
    <br><br>
    <a href="admin_register.php" class="back-link" style="color: var(--primary-orange);">
        <i class="fas fa-user-plus"></i> Регистрация админа
    </a>
</div>

</body>
</html>
