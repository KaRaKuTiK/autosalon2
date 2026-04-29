<?php
require_once 'config.php';
$admin = checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Онлайн чат — Админ-панель</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg:       #0a0a0a;
            --bg2:      #111;
            --bg3:      #1a1a1a;
            --orange:   #ff6600;
            --orange2:  #ff8533;
            --text:     #ffffff;
            --muted:    #888;
            --border:   rgba(255,102,0,0.2);
            --card:     rgba(26,26,26,0.9);
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Roboto',sans-serif; }
        body {
            background: linear-gradient(135deg, var(--bg) 0%, var(--bg2) 50%, #1a0f00 100%);
            color: var(--text);
            min-height: 100vh;
        }
        /* Header */
        .header { background: rgba(10,10,10,.95); backdrop-filter: blur(10px); padding: 20px 0; border-bottom: 2px solid var(--orange); box-shadow: 0 4px 20px rgba(255,102,0,.2); }
        .container { width: 90%; max-width: 1600px; margin: 0 auto; }
        .header-content { display:flex; justify-content:space-between; align-items:center; }
        .logo { display:flex; align-items:center; gap:15px; }
        .logo-icon { font-size:2em; color:var(--orange); }
        .logo-text { color:var(--orange); font-size:1.8em; font-weight:700; }
        .admin-info { display:flex; align-items:center; gap:20px; }
        .admin-name { color:var(--muted); }
        .admin-name strong { color:var(--orange); }
        .btn-logout { padding:10px 20px; background:rgba(220,53,69,.2); color:#ff6b6b; border:1px solid rgba(220,53,69,.3); border-radius:10px; text-decoration:none; font-weight:600; display:flex; align-items:center; gap:8px; transition:background .3s; }
        .btn-logout:hover { background:rgba(220,53,69,.35); }
        /* Layout */
        .chat-layout { display: grid; grid-template-columns: 320px 1fr; height: calc(100vh - 130px); overflow: hidden; }
        /* Sidebar */
        .sessions-panel {
            background: rgba(15,15,15,.95);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .sessions-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,102,0,.05);
        }
        .sessions-header h2 { font-size:1em; font-weight:600; color:var(--text); display:flex; align-items:center; gap:8px; }
        .sessions-header h2 i { color:var(--orange); }
        .sessions-count-badge {
            background: var(--orange);
            color:#fff;
            border-radius: 12px;
            padding: 2px 9px;
            font-size: .75em;
            font-weight: 700;
        }
        .sessions-search {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
        }
        .sessions-search input {
            width:100%;
            padding: 8px 12px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: .88em;
            outline: none;
        }
        .sessions-search input:focus { border-color: var(--orange); }
        .sessions-list { flex:1; overflow-y:auto; }
        .sessions-list::-webkit-scrollbar { width:4px; }
        .sessions-list::-webkit-scrollbar-thumb { background: rgba(255,102,0,.3); border-radius:2px; }

        .session-item {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255,255,255,.04);
            cursor: pointer;
            transition: background .2s;
            position: relative;
        }
        .session-item:hover { background: rgba(255,102,0,.07); }
        .session-item.active { background: rgba(255,102,0,.12); border-left: 3px solid var(--orange); }
        .session-item-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; }
        .session-name { font-weight:600; font-size:.92em; color:var(--text); }
        .session-time { font-size:.72em; color:var(--muted); }
        .session-preview { font-size:.8em; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .session-unread {
            position:absolute; top:14px; right:14px;
            background:var(--orange); color:#fff;
            border-radius:50%; width:20px; height:20px;
            font-size:.7em; font-weight:700;
            display:flex; align-items:center; justify-content:center;
        }
        .session-status { display:inline-block; padding:2px 7px; border-radius:8px; font-size:.7em; font-weight:600; margin-left:6px; }
        .status-open   { background:rgba(76,175,80,.15); color:#4caf50; }
        .status-closed { background:rgba(100,100,100,.15); color:#666; }
        .sessions-empty { text-align:center; padding:40px 20px; color:var(--muted); font-size:.9em; }
        .sessions-empty i { font-size:2.5em; color:rgba(255,102,0,.3); margin-bottom:12px; display:block; }

        /* Main Chat Area */
        .chat-main {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-main-empty {
            flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;
            color:var(--muted);
        }
        .chat-main-empty i { font-size:4em; color:rgba(255,102,0,.2); margin-bottom:20px; }
        .chat-main-empty p { font-size:1em; }

        /* Chat Window */
        #active-chat { display:none; flex-direction:column; flex:1; overflow:hidden; }

        .chat-window-header {
            padding: 14px 20px;
            background: rgba(15,15,15,.9);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }
        .chat-window-avatar {
            width:42px; height:42px;
            background: rgba(255,102,0,.15);
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:var(--orange); font-size:1.2em;
            flex-shrink:0;
        }
        .chat-window-info { flex:1; }
        .chat-window-name { font-weight:700; font-size:1em; }
        .chat-window-sub { font-size:.78em; color:var(--muted); margin-top:2px; }
        .chat-window-actions { display:flex; gap:8px; }
        .btn-close-chat {
            padding: 8px 16px;
            background: rgba(220,53,69,.15);
            color: #ff6b6b;
            border: 1px solid rgba(220,53,69,.3);
            border-radius: 8px;
            cursor: pointer;
            font-size: .85em;
            display: flex; align-items: center; gap:6px;
            transition: background .2s;
        }
        .btn-close-chat:hover { background: rgba(220,53,69,.3); }

        /* Messages */
        .chat-messages-area {
            flex:1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .chat-messages-area::-webkit-scrollbar { width:5px; }
        .chat-messages-area::-webkit-scrollbar-thumb { background:rgba(255,102,0,.3); border-radius:3px; }

        .admin-msg { display:flex; flex-direction:column; max-width:70%; }
        .admin-msg.user { align-self:flex-start; align-items:flex-start; }
        .admin-msg.admin { align-self:flex-end; align-items:flex-end; }
        .admin-msg-bubble {
            padding:11px 16px;
            border-radius:18px;
            font-size:.91em;
            line-height:1.45;
            word-break:break-word;
        }
        .admin-msg.user .admin-msg-bubble {
            background:rgba(255,255,255,.08);
            color:#ddd;
            border:1px solid rgba(255,102,0,.15);
            border-bottom-left-radius:4px;
        }
        .admin-msg.admin .admin-msg-bubble {
            background:linear-gradient(135deg,var(--orange),var(--orange2));
            color:#fff;
            border-bottom-right-radius:4px;
        }
        .admin-msg-meta { font-size:.72em; color:var(--muted); margin-top:4px; display:flex; gap:5px; align-items:center; }
        .admin-msg-read { font-size:1.1em; color:#4caf50; font-weight:bold; }
        .admin-msg-file {
            display:flex; align-items:center; gap:8px;
            padding:9px 13px;
            background:rgba(255,102,0,.1);
            border:1px solid rgba(255,102,0,.25);
            border-radius:10px;
            text-decoration:none;
            color:var(--orange2);
            font-size:.84em;
            margin-top:4px;
            word-break:break-all;
        }
        .admin-msg-file:hover { background:rgba(255,102,0,.2); }
        .admin-msg-img { max-width:220px; max-height:180px; border-radius:10px; object-fit:cover; margin-top:4px; cursor:pointer; border:1px solid rgba(255,102,0,.3); }
        .system-msg { text-align:center; color:var(--muted); font-size:.78em; padding:4px 0; }

        /* Reply area */
        .chat-reply-area {
            padding: 14px 18px;
            border-top: 1px solid var(--border);
            background: rgba(10,10,10,.7);
            flex-shrink: 0;
        }
        .chat-reply-row { display:flex; gap:10px; align-items:flex-end; }
        .reply-textarea {
            flex:1;
            padding: 11px 15px;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: .9em;
            resize: none;
            outline: none;
            line-height: 1.4;
            max-height: 100px;
            min-height: 42px;
            font-family: inherit;
            transition: border-color .25s;
        }
        .reply-textarea:focus { border-color: var(--orange); }
        .reply-textarea::placeholder { color:#444; }
        .btn-send-reply {
            padding: 10px 20px;
            background: linear-gradient(135deg,var(--orange),var(--orange2));
            color:#fff;
            border:none;
            border-radius:12px;
            font-size:.9em;
            font-weight:600;
            cursor:pointer;
            display:flex; align-items:center; gap:7px;
            transition:opacity .2s,transform .15s;
            flex-shrink:0;
            box-shadow:0 3px 12px rgba(255,102,0,.35);
        }
        .btn-send-reply:hover { opacity:.9; transform:translateY(-1px); }
        .btn-send-reply:disabled { opacity:.5; cursor:not-allowed; }
        .chat-closed-bar { text-align:center; padding:12px; color:#e74c3c; font-size:.85em; display:none; }
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
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Выйти
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_nav.php'; ?>

<div class="chat-layout">
    <!-- Список сессий -->
    <div class="sessions-panel">
        <div class="sessions-header">
            <h2><i class="fas fa-comments"></i> Чаты</h2>
            <span class="sessions-count-badge" id="sessions-count">0</span>
        </div>
        <div class="sessions-search">
            <input type="text" id="search-sessions" placeholder="🔍 Поиск по имени...">
        </div>
        <div class="sessions-list" id="sessions-list">
            <div class="sessions-empty">
                <i class="fas fa-comment-slash"></i>
                <p>Нет активных чатов</p>
            </div>
        </div>
    </div>

    <!-- Основная область -->
    <div class="chat-main">
        <!-- Заглушка -->
        <div class="chat-main-empty" id="chat-empty">
            <i class="fas fa-comment-dots"></i>
            <p>Выберите чат из списка слева</p>
        </div>

        <!-- Окно активного чата -->
        <div id="active-chat">
            <div class="chat-window-header">
                <div class="chat-window-avatar"><i class="fas fa-user"></i></div>
                <div class="chat-window-info">
                    <div class="chat-window-name" id="cw-name">—</div>
                    <div class="chat-window-sub" id="cw-sub">—</div>
                </div>
                <div class="chat-window-actions">
                    <button class="btn-close-chat" id="btn-close-session" title="Закрыть чат">
                        <i class="fas fa-times-circle"></i> Закрыть чат
                    </button>
                </div>
            </div>
            <div class="chat-messages-area" id="admin-messages"></div>
            <div class="chat-closed-bar" id="chat-closed-bar">
                <i class="fas fa-lock"></i> Чат закрыт
            </div>
            <div class="chat-reply-area" id="reply-area">
                <div class="chat-reply-row">
                    <textarea class="reply-textarea" id="reply-text" placeholder="Введите ответ..." rows="1"></textarea>
                    <button class="btn-send-reply" id="btn-send-reply">
                        <i class="fas fa-paper-plane"></i> Отправить
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ADMIN_API = 'chat_api.php';
const POLL_INTERVAL = 3000;

let currentSession = null;
let lastMsgId = 0;
let pollTimer = null;
let allSessions = [];

const sessionsList  = document.getElementById('sessions-list');
const sessionsCount = document.getElementById('sessions-count');
const searchInput   = document.getElementById('search-sessions');
const chatEmpty     = document.getElementById('chat-empty');
const activeChat    = document.getElementById('active-chat');
const adminMessages = document.getElementById('admin-messages');
const cwName        = document.getElementById('cw-name');
const cwSub         = document.getElementById('cw-sub');
const replyText     = document.getElementById('reply-text');
const btnSendReply  = document.getElementById('btn-send-reply');
const btnCloseSession = document.getElementById('btn-close-session');
const replyArea     = document.getElementById('reply-area');
const chatClosedBar = document.getElementById('chat-closed-bar');

// ─── Загрузка списка сессий ───────────────────────────────────────────────
async function loadSessions() {
    const res = await apiGet({ action: 'get_sessions' });
    if (!res.success) return;
    allSessions = res.sessions;
    renderSessionsList(allSessions);
}

function renderSessionsList(sessions) {
    sessionsCount.textContent = sessions.length;
    if (!sessions.length) {
        sessionsList.innerHTML = '<div class="sessions-empty"><i class="fas fa-comment-slash"></i><p>Нет активных чатов</p></div>';
        return;
    }
    sessionsList.innerHTML = '';
    sessions.forEach(s => {
        const el = document.createElement('div');
        el.className = 'session-item' + (currentSession && currentSession.id == s.id ? ' active' : '');
        el.dataset.id = s.id;
        const lastMsgTime = s.last_message_time ? formatTime(s.last_message_time) : '';
        const statusBadge = s.status === 'open'
            ? '<span class="session-status status-open">открыт</span>'
            : '<span class="session-status status-closed">закрыт</span>';
        el.innerHTML = `
            <div class="session-item-header">
                <span class="session-name">${escHtml(s.guest_name)}${statusBadge}</span>
                <span class="session-time">${lastMsgTime}</span>
            </div>
            <div class="session-preview">${escHtml(s.last_message || 'Нет сообщений')}</div>
            ${s.unread_count > 0 ? `<div class="session-unread">${s.unread_count}</div>` : ''}
        `;
        el.addEventListener('click', () => openSession(s));
        sessionsList.appendChild(el);
    });
}

searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    renderSessionsList(allSessions.filter(s => s.guest_name.toLowerCase().includes(q)));
});

// ─── Открытие чата ────────────────────────────────────────────────────────
async function openSession(session) {
    currentSession = session;
    lastMsgId = 0;
    adminMessages.innerHTML = '';
    chatEmpty.style.display = 'none';
    activeChat.style.display = 'flex';
    cwName.textContent = session.guest_name;
    cwSub.textContent = session.guest_email ? `📧 ${session.guest_email}` : `Гость #${session.id}`;

    if (session.status === 'closed') {
        replyArea.style.display = 'none';
        chatClosedBar.style.display = 'block';
    } else {
        replyArea.style.display = 'block';
        chatClosedBar.style.display = 'none';
        replyText.focus();
    }

    // Обновляем активную сессию в боковой панели
    document.querySelectorAll('.session-item').forEach(el => {
        el.classList.toggle('active', el.dataset.id == session.id);
    });

    stopPolling();
    await fetchAdminMessages();
    startPolling();
}

// ─── Получение сообщений ──────────────────────────────────────────────────
async function fetchAdminMessages() {
    if (!currentSession) return;
    const res = await apiGet({ action: 'get_messages', session_id: currentSession.id, after: lastMsgId });
    if (!res.success) return;

    res.messages.forEach(msg => {
        renderAdminMessage(msg);
        if (msg.id > lastMsgId) lastMsgId = msg.id;
    });

    if (res.read_up_to) {
        document.querySelectorAll('.admin-msg.admin').forEach(el => {
            const id = parseInt(el.dataset.id);
            if (id <= res.read_up_to) {
                const metaBlock = el.querySelector('.admin-msg-meta');
                if (metaBlock && !metaBlock.querySelector('.admin-msg-read')) {
                    metaBlock.innerHTML += '<span class="admin-msg-read" title="Прочитано">✓✓</span>';
                }
            }
        });
    }

    adminMessages.scrollTop = adminMessages.scrollHeight;
}

function renderAdminMessage(msg) {
    const el = document.createElement('div');
    el.className = `admin-msg ${msg.sender}`;
    el.dataset.id = msg.id;

    const senderLabel = msg.sender === 'admin' ? 'Вы (Администратор)' : (currentSession?.guest_name || 'Пользователь');
    const timeStr = formatTime(msg.created_at);

    let content = '';
    if (msg.message) {
        content += `<div class="admin-msg-bubble">${escHtml(msg.message)}</div>`;
    }
    if (msg.file_path) {
        const isImage = msg.file_type && msg.file_type.startsWith('image/');
        if (isImage) {
            content += `<img class="admin-msg-img" src="../${escHtml(msg.file_path)}" alt="${escHtml(msg.file_name || 'img')}" onclick="window.open(this.src,'_blank')">`;
        } else {
            content += `<a class="admin-msg-file" href="../${escHtml(msg.file_path)}" target="_blank"><i class="fas fa-file"></i>${escHtml(msg.file_name || 'Файл')}</a>`;
        }
    }
    let metaContent = `${escHtml(senderLabel)} · ${timeStr}`;
    if (msg.sender === 'admin' && msg.is_read == 1) {
        metaContent += `<span class="admin-msg-read" title="Прочитано">✓✓</span>`;
    }
    content += `<div class="admin-msg-meta">${metaContent}</div>`;
    el.innerHTML = content;
    adminMessages.appendChild(el);
}

// ─── Отправка ответа ──────────────────────────────────────────────────────
btnSendReply.addEventListener('click', sendReply);
replyText.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendReply(); }
    autoResize(replyText);
});
replyText.addEventListener('input', () => autoResize(replyText));

async function sendReply() {
    if (!currentSession) return;
    const text = replyText.value.trim();
    if (!text) return;
    btnSendReply.disabled = true;
    replyText.value = '';
    replyText.style.height = 'auto';
    const fd = new FormData();
    fd.append('action', 'send_reply');
    fd.append('session_id', currentSession.id);
    fd.append('message', text);
    await fetch(ADMIN_API, { method: 'POST', body: fd });
    await fetchAdminMessages();
    btnSendReply.disabled = false;
    replyText.focus();
}

// ─── Закрытие сессии ──────────────────────────────────────────────────────
btnCloseSession.addEventListener('click', async () => {
    if (!currentSession || !confirm('Закрыть этот чат?')) return;
    const fd = new FormData();
    fd.append('action', 'close_session');
    fd.append('session_id', currentSession.id);
    await fetch(ADMIN_API, { method: 'POST', body: fd });
    currentSession.status = 'closed';
    replyArea.style.display = 'none';
    chatClosedBar.style.display = 'block';
    loadSessions();
    stopPolling();
});

// ─── Polling ──────────────────────────────────────────────────────────────
function startPolling() {
    if (pollTimer) return;
    pollTimer = setInterval(() => {
        fetchAdminMessages();
        loadSessions();
    }, POLL_INTERVAL);
}
function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

// ─── Утилиты ──────────────────────────────────────────────────────────────
function autoResize(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 100) + 'px'; }
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatTime(ts) {
    if (!ts) return '';
    const d = new Date(ts.replace(' ','T'));
    return d.toLocaleString('ru-RU',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'});
}
async function apiGet(params) {
    const url = ADMIN_API + '?' + new URLSearchParams(params);
    try { const r = await fetch(url); return await r.json(); } catch(e) { return {success:false}; }
}

// Старт
loadSessions();
setInterval(loadSessions, 8000);
</script>
</body>
</html>
