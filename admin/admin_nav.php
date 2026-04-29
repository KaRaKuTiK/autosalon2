<!-- admin_nav.php -->
<style>
.admin-navbar {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #111;
    padding: 10px 20px;
    border-bottom: 1px solid rgba(255, 102, 0, 0.3);
    gap: 15px;
    overflow-x: auto;
}
.admin-navbar .nav-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #b0b0b0;
    text-decoration: none;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 20px;
    transition: all 0.3s ease;
    white-space: nowrap;
    border: 1px solid transparent;
}
.admin-navbar .nav-tab:hover {
    color: #fff;
    background: rgba(255, 102, 0, 0.1);
    border-color: rgba(255, 102, 0, 0.2);
}
.admin-navbar .nav-tab.active {
    background: #ff6600;
    color: #fff;
    border-color: #ff6600;
}
.admin-navbar .nav-tab i {
    font-size: 1.1em;
}
</style>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="admin-navbar">
    <a href="index.php" class="nav-tab <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-pie"></i> Дашборд
    </a>
    <a href="users.php" class="nav-tab <?php echo $currentPage == 'users.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> Пользователи
    </a>
    <a href="cars_list.php" class="nav-tab <?php echo in_array($currentPage, ['cars_list.php', 'car_edit.php', 'car_add.php']) ? 'active' : ''; ?>">
        <i class="fas fa-car"></i> Автомобили
    </a>
    <a href="salons.php" class="nav-tab <?php echo $currentPage == 'salons.php' ? 'active' : ''; ?>">
        <i class="fas fa-building"></i> Салоны
    </a>
    <a href="inventory.php" class="nav-tab <?php echo $currentPage == 'inventory.php' ? 'active' : ''; ?>">
        <i class="fas fa-boxes"></i> Инвентарь
    </a>
    <a href="feedback.php" class="nav-tab <?php echo $currentPage == 'feedback.php' ? 'active' : ''; ?>">
        <i class="fas fa-comments"></i> Обратная связь
    </a>
    <a href="profile.php" class="nav-tab <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-edit"></i> Профиль
    </a>
    <a href="chat.php" class="nav-tab <?php echo $currentPage == 'chat.php' ? 'active' : ''; ?>" id="nav-chat-link" style="position:relative;">
        <i class="fas fa-comment-dots"></i> Онлайн чат
        <span id="nav-chat-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:#e74c3c;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65em;font-weight:700;align-items:center;justify-content:center;border:2px solid #111;"></span>
    </a>
    <a href="../index.php" class="nav-tab" style="margin-left: 20px;">
        <i class="fas fa-arrow-left"></i> На сайт
    </a>
</div>
<script>
(function() {
    function checkUnread() {
        fetch('chat_api.php?action=get_unread_count')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('nav-chat-badge');
                if (!badge) return;
                if (data.count > 0) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }).catch(() => {});
    }
    checkUnread();
    setInterval(checkUnread, 8000);
})();
</script>
