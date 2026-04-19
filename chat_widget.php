<?php
/**
 * chat_widget.php
 * Подключается в конце <body> на любой странице сайта.
 * Показывает кнопку чата внизу справа и всплывающее окно.
 */
$isLoggedIn = isset($_SESSION['user']);
$userName = $isLoggedIn ? htmlspecialchars($_SESSION['user']['full_name']) : '';
$userEmail = $isLoggedIn ? htmlspecialchars($_SESSION['user']['email'] ?? '') : '';
?>

<!-- ═══════════════════════════ CHAT WIDGET ═══════════════════════════ -->
<style>
/* ── Кнопка-триггер ── */
#chat-trigger {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 62px;
    height: 62px;
    background: linear-gradient(135deg, #ff6600, #ff8533);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 9998;
    box-shadow: 0 6px 24px rgba(255,102,0,0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    outline: none;
}
#chat-trigger:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 30px rgba(255,102,0,0.65);
}
#chat-trigger i {
    color: #fff;
    font-size: 1.5em;
    transition: transform 0.3s;
}
#chat-trigger.open i { transform: rotate(90deg); }

/* Бейдж непрочитанных */
#chat-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #e74c3c;
    color: #fff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.7em;
    font-weight: 700;
    display: none;
    align-items: center;
    justify-content: center;
    border: 2px solid #0a0a0a;
}

/* ── Всплывающее окно ── */
#chat-popup {
    position: fixed;
    bottom: 105px;
    right: 30px;
    width: 380px;
    max-width: calc(100vw - 40px);
    height: 540px;
    max-height: calc(100vh - 130px);
    background: #111;
    border-radius: 20px;
    border: 1px solid rgba(255,102,0,0.35);
    box-shadow: 0 16px 60px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,102,0,0.15);
    display: flex;
    flex-direction: column;
    z-index: 9999;
    overflow: hidden;
    transform: scale(0.85) translateY(30px);
    opacity: 0;
    pointer-events: none;
    transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease;
    transform-origin: bottom right;
}
#chat-popup.visible {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* Шапка */
.chat-header {
    background: linear-gradient(135deg, #1a0a00, #2a1000);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255,102,0,0.3);
    flex-shrink: 0;
}
.chat-header-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,102,0,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff6600;
    font-size: 1.2em;
    flex-shrink: 0;
}
.chat-header-info { flex: 1; }
.chat-header-title { color: #fff; font-weight: 700; font-size: 1em; }
.chat-header-status { color: #4caf50; font-size: 0.78em; display: flex; align-items: center; gap: 5px; }
.chat-header-status::before {
    content: '';
    width: 7px; height: 7px;
    background: #4caf50;
    border-radius: 50%;
    display: inline-block;
}
.chat-header-close {
    background: rgba(255,255,255,0.08);
    border: none;
    color: #b0b0b0;
    cursor: pointer;
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s, color 0.2s;
    font-size: 0.9em;
}
.chat-header-close:hover { background: rgba(220,53,69,0.3); color: #ff6b6b; }

/* Экран приветствия (для незарегистрированных) */
#chat-welcome {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 24px;
    gap: 14px;
}
#chat-welcome .welcome-icon { font-size: 3em; color: #ff6600; }
#chat-welcome h3 { color: #fff; font-size: 1.15em; text-align: center; }
#chat-welcome p { color: #888; font-size: 0.9em; text-align: center; margin-bottom: 8px; }
.chat-input-field {
    width: 100%;
    padding: 12px 15px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,102,0,0.25);
    border-radius: 10px;
    color: #fff;
    font-size: 0.93em;
    outline: none;
    transition: border-color 0.25s;
}
.chat-input-field:focus { border-color: #ff6600; }
.chat-start-btn {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #ff6600, #ff8533);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.2s;
    margin-top: 6px;
}
.chat-start-btn:hover { opacity: 0.9; transform: translateY(-1px); }

/* Область сообщений */
#chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}
#chat-messages::-webkit-scrollbar { width: 4px; }
#chat-messages::-webkit-scrollbar-thumb { background: rgba(255,102,0,0.35); border-radius: 2px; }

/* Сообщения */
.chat-msg {
    display: flex;
    flex-direction: column;
    max-width: 80%;
    animation: msgIn 0.25s ease;
}
@keyframes msgIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.chat-msg.user { align-self: flex-end; align-items: flex-end; }
.chat-msg.admin { align-self: flex-start; align-items: flex-start; }

.msg-bubble {
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 0.9em;
    line-height: 1.45;
    word-break: break-word;
}
.chat-msg.user .msg-bubble {
    background: linear-gradient(135deg, #ff6600, #ff8533);
    color: #fff;
    border-bottom-right-radius: 4px;
}
.chat-msg.admin .msg-bubble {
    background: rgba(255,255,255,0.08);
    color: #e0e0e0;
    border: 1px solid rgba(255,102,0,0.2);
    border-bottom-left-radius: 4px;
}
.msg-time { font-size: 0.72em; color: #555; margin-top: 3px; }

/* Файлы в чате */
.msg-file {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(255,102,0,0.1);
    border: 1px solid rgba(255,102,0,0.25);
    border-radius: 10px;
    text-decoration: none;
    color: #ff8533;
    font-size: 0.85em;
    margin-top: 4px;
    transition: background 0.2s;
    max-width: 100%;
    word-break: break-all;
}
.msg-file:hover { background: rgba(255,102,0,0.2); }
.msg-file i { font-size: 1.1em; flex-shrink: 0; }
.msg-img {
    max-width: 200px;
    max-height: 160px;
    border-radius: 10px;
    object-fit: cover;
    margin-top: 4px;
    cursor: pointer;
    border: 1px solid rgba(255,102,0,0.3);
}

/* Typing indicator */
.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 10px 14px;
    background: rgba(255,255,255,0.06);
    border-radius: 18px;
    border-bottom-left-radius: 4px;
    width: fit-content;
}
.typing-dot {
    width: 7px; height: 7px;
    background: #ff6600;
    border-radius: 50%;
    animation: typingBounce 1.2s infinite;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingBounce {
    0%,60%,100% { transform: translateY(0); }
    30% { transform: translateY(-6px); }
}

/* Ввод сообщения */
.chat-input-area {
    padding: 12px 14px;
    border-top: 1px solid rgba(255,102,0,0.2);
    flex-shrink: 0;
    background: rgba(255,255,255,0.02);
}
.chat-input-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
}
.chat-text-input {
    flex: 1;
    padding: 10px 14px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,102,0,0.25);
    border-radius: 12px;
    color: #fff;
    font-size: 0.9em;
    resize: none;
    outline: none;
    line-height: 1.4;
    max-height: 90px;
    min-height: 40px;
    font-family: inherit;
    transition: border-color 0.25s;
}
.chat-text-input:focus { border-color: #ff6600; }
.chat-text-input::placeholder { color: #555; }
.chat-icon-btn {
    width: 38px; height: 38px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1em;
    transition: background 0.2s, transform 0.15s;
    flex-shrink: 0;
}
.chat-icon-btn:hover { transform: scale(1.08); }
.btn-attach {
    background: rgba(255,255,255,0.07);
    color: #888;
    border: 1px solid rgba(255,255,255,0.1);
}
.btn-attach:hover { background: rgba(255,102,0,0.15); color: #ff6600; }
.btn-send {
    background: linear-gradient(135deg, #ff6600, #ff8533);
    color: #fff;
    box-shadow: 0 3px 10px rgba(255,102,0,0.35);
}
.btn-send:hover { box-shadow: 0 4px 14px rgba(255,102,0,0.5); }
.btn-send:disabled { opacity: 0.5; cursor: not-allowed; }

/* Preview прикреплённого файла */
#chat-file-preview {
    display: none;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 7px 10px;
    background: rgba(255,102,0,0.08);
    border-radius: 8px;
    border: 1px solid rgba(255,102,0,0.2);
    font-size: 0.83em;
    color: #ff8533;
}
#chat-file-preview .remove-file {
    cursor: pointer;
    color: #e74c3c;
    margin-left: auto;
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 0.9em;
}
#chat-file-preview .remove-file:hover { background: rgba(231,76,60,0.15); }

/* Статус "закрыт" */
#chat-closed-notice {
    display: none;
    text-align: center;
    padding: 10px;
    color: #e74c3c;
    font-size: 0.85em;
}

/* Скроллбар */
#chat-messages { scrollbar-width: thin; scrollbar-color: rgba(255,102,0,0.35) transparent; }

/* Адаптив */
@media(max-width: 480px) {
    #chat-popup { width: calc(100vw - 20px); right: 10px; bottom: 90px; }
    #chat-trigger { right: 15px; bottom: 15px; }
}
</style>

<!-- Кнопка чата -->
<button id="chat-trigger" title="Онлайн-чат" aria-label="Открыть чат">
    <i class="fas fa-comments"></i>
    <span id="chat-badge" aria-label="Новые сообщения"></span>
</button>

<!-- Всплывающий чат -->
<div id="chat-popup" role="dialog" aria-label="Чат поддержки" aria-hidden="true">

    <!-- Шапка -->
    <div class="chat-header">
        <div class="chat-header-avatar"><i class="fas fa-headset"></i></div>
        <div class="chat-header-info">
            <div class="chat-header-title">Онлайн-поддержка</div>
            <div class="chat-header-status">Онлайн — ответим быстро</div>
        </div>
        <button class="chat-header-close" id="chat-close-btn" title="Закрыть"><i class="fas fa-times"></i></button>
    </div>

    <!-- Форма гостя (если не авторизован) -->
    <div id="chat-welcome" <?php echo $isLoggedIn ? 'style="display:none"' : ''; ?>>
        <div class="welcome-icon"><i class="fas fa-comment-dots"></i></div>
        <h3>Задайте нам вопрос!</h3>
        <p>Представьтесь, чтобы мы могли к вам обратиться</p>
        <input class="chat-input-field" id="guest-name" type="text" placeholder="Ваше имя *" required value="">
        <input class="chat-input-field" id="guest-email" type="email" placeholder="Email (необязательно)" value="">
        <button class="chat-start-btn" id="chat-start-btn">
            <i class="fas fa-arrow-right"></i> Начать чат
        </button>
    </div>

    <!-- Тело чата -->
    <div id="chat-body" style="display:flex; flex-direction:column; flex:1; overflow:hidden; <?php echo $isLoggedIn ? '' : 'display:none!important'; ?>">
        <div id="chat-messages"></div>
        <div id="chat-closed-notice">
            <i class="fas fa-lock"></i> Чат завершён администратором
        </div>
        <div class="chat-input-area" id="chat-input-area">
            <div id="chat-file-preview">
                <i class="fas fa-paperclip"></i>
                <span id="chat-file-name">файл</span>
                <span class="remove-file" id="remove-file-btn" title="Убрать файл"><i class="fas fa-times"></i></span>
            </div>
            <div class="chat-input-row">
                <textarea class="chat-text-input" id="chat-text" placeholder="Напишите сообщение..." rows="1"></textarea>
                <label class="chat-icon-btn btn-attach" title="Прикрепить файл">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" id="chat-file-input" style="display:none" accept="image/*,.pdf,.doc,.docx,.txt">
                </label>
                <button class="chat-icon-btn btn-send" id="chat-send-btn" title="Отправить">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
(function() {
'use strict';
const API = 'chat_api.php';
const POLL_INTERVAL = 3000;

let token = sessionStorage.getItem('chat_token') || null;
let lastMsgId = 0;
let pollTimer = null;
let isOpen = false;
let chatClosed = false;
let pendingFile = null;
let unreadCount = 0;

const trigger = document.getElementById('chat-trigger');
const popup   = document.getElementById('chat-popup');
const badge   = document.getElementById('chat-badge');
const closeBtn = document.getElementById('chat-close-btn');
const welcomeEl = document.getElementById('chat-welcome');
const chatBody  = document.getElementById('chat-body');
const messagesEl = document.getElementById('chat-messages');
const textInput  = document.getElementById('chat-text');
const sendBtn    = document.getElementById('chat-send-btn');
const fileInput  = document.getElementById('chat-file-input');
const filePreview = document.getElementById('chat-file-preview');
const fileNameEl  = document.getElementById('chat-file-name');
const removeFileBtn = document.getElementById('remove-file-btn');
const closedNotice = document.getElementById('chat-closed-notice');
const inputArea = document.getElementById('chat-input-area');

// Если авторизован — сразу показываем тело чата
<?php if ($isLoggedIn): ?>
chatBody.style.display = 'flex';
chatBody.style.flexDirection = 'column';
chatBody.style.flex = '1';
chatBody.style.overflow = 'hidden';
welcomeEl.style.display = 'none';
<?php endif; ?>

trigger.addEventListener('click', toggleChat);
closeBtn.addEventListener('click', () => { isOpen = false; popup.classList.remove('visible'); popup.setAttribute('aria-hidden','true'); trigger.classList.remove('open'); });

// Переключение
function toggleChat() {
    isOpen = !isOpen;
    popup.classList.toggle('visible', isOpen);
    popup.setAttribute('aria-hidden', !isOpen);
    trigger.classList.toggle('open', isOpen);
    if (isOpen) {
        unreadCount = 0;
        updateBadge();
        if (token) {
            startPolling();
            textInput && textInput.focus();
        }
    } else {
        stopPolling();
    }
}

// ─── ГОСТЬ: начало сессии ────────────────────────────────────────────────
const startBtn = document.getElementById('chat-start-btn');
if (startBtn) {
    startBtn.addEventListener('click', async () => {
        const name  = (document.getElementById('guest-name')?.value || '').trim();
        const email = (document.getElementById('guest-email')?.value || '').trim();
        if (!name) { document.getElementById('guest-name').style.borderColor = '#e74c3c'; return; }
        startBtn.disabled = true;
        startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Подключение...';

        const res = await postData({ action: 'init_session', guest_name: name, guest_email: email });
        if (res.success) {
            token = res.token;
            sessionStorage.setItem('chat_token', token);
            welcomeEl.style.display = 'none';
            chatBody.style.display = 'flex';
            chatBody.style.flexDirection = 'column';
            chatBody.style.flex = '1';
            chatBody.style.overflow = 'hidden';
            appendSystemMsg('Добрый день! Чем можем помочь? 😊');
            startPolling();
        } else {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Начать чат';
        }
    });
}

// ─── ПОЛЬЗОВАТЕЛЬ: авторизован — инициализируем сессию ─────────────────
<?php if ($isLoggedIn): ?>
(async function() {
    if (!token) {
        const res = await postData({ action: 'init_session', guest_name: <?php echo json_encode($userName); ?>, guest_email: <?php echo json_encode($userEmail); ?> });
        if (res.success) {
            token = res.token;
            sessionStorage.setItem('chat_token', token);
        }
    }
    startPolling();
})();
<?php endif; ?>

// ─── ОТПРАВКА СООБЩЕНИЯ ──────────────────────────────────────────────────
sendBtn.addEventListener('click', sendMessage);
textInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    autoResize();
});
textInput.addEventListener('input', autoResize);

function autoResize() {
    textInput.style.height = 'auto';
    textInput.style.height = Math.min(textInput.scrollHeight, 90) + 'px';
}

fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) {
        pendingFile = fileInput.files[0];
        fileNameEl.textContent = pendingFile.name;
        filePreview.style.display = 'flex';
    }
});
removeFileBtn.addEventListener('click', () => {
    pendingFile = null;
    fileInput.value = '';
    filePreview.style.display = 'none';
});

async function sendMessage() {
    if (chatClosed || !token) return;
    const text = textInput.value.trim();
    if (!text && !pendingFile) return;

    sendBtn.disabled = true;
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('token', token);
    if (text) fd.append('message', text);
    if (pendingFile) fd.append('file', pendingFile);

    textInput.value = '';
    textInput.style.height = 'auto';
    pendingFile = null;
    fileInput.value = '';
    filePreview.style.display = 'none';

    const res = await fetchJSON(API, { method: 'POST', body: fd });
    if (res.success) {
        await fetchMessages();
    }
    sendBtn.disabled = false;
    textInput.focus();
}

// ─── POLLING ──────────────────────────────────────────────────────────────
function startPolling() {
    if (pollTimer) return;
    fetchMessages();
    pollTimer = setInterval(fetchMessages, POLL_INTERVAL);
}
function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

async function fetchMessages() {
    if (!token) return;
    const url = `${API}?action=get_messages&token=${encodeURIComponent(token)}&after=${lastMsgId}`;
    const res = await fetchJSON(url);
    if (!res.success) return;

    res.messages.forEach(msg => {
        renderMessage(msg);
        if (msg.id > lastMsgId) lastMsgId = msg.id;
        if (msg.sender === 'admin' && !isOpen) {
            unreadCount++;
            updateBadge();
        }
    });

    if (res.status === 'closed' && !chatClosed) {
        chatClosed = true;
        closedNotice.style.display = 'block';
        inputArea.style.display = 'none';
        stopPolling();
    }
}

// ─── РЕНДЕР СООБЩЕНИЙ ─────────────────────────────────────────────────────
function renderMessage(msg) {
    const el = document.createElement('div');
    el.className = `chat-msg ${msg.sender}`;
    el.dataset.id = msg.id;

    let content = '';
    if (msg.message) {
        content += `<div class="msg-bubble">${escapeHtml(msg.message)}</div>`;
    }
    if (msg.file_path) {
        const isImage = msg.file_type && msg.file_type.startsWith('image/');
        if (isImage) {
            content += `<img class="msg-img" src="${escapeHtml(msg.file_path)}" alt="${escapeHtml(msg.file_name || 'изображение')}" onclick="window.open(this.src,'_blank')">`;
        } else {
            const icon = fileIcon(msg.file_type);
            content += `<a class="msg-file" href="${escapeHtml(msg.file_path)}" target="_blank" rel="noopener"><i class="fas ${icon}"></i>${escapeHtml(msg.file_name || 'Файл')}</a>`;
        }
    }

    const d = new Date(msg.created_at.replace(' ', 'T'));
    const timeStr = d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
    content += `<div class="msg-time">${timeStr}</div>`;
    el.innerHTML = content;
    messagesEl.appendChild(el);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function appendSystemMsg(text) {
    const el = document.createElement('div');
    el.style.cssText = 'text-align:center;color:#555;font-size:0.8em;padding:8px 0;';
    el.textContent = text;
    messagesEl.appendChild(el);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function updateBadge() {
    if (unreadCount > 0) {
        badge.style.display = 'flex';
        badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
    } else {
        badge.style.display = 'none';
    }
}

// ─── УТИЛИТЫ ──────────────────────────────────────────────────────────────
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fileIcon(mime) {
    if (!mime) return 'fa-file';
    if (mime.includes('pdf')) return 'fa-file-pdf';
    if (mime.includes('word') || mime.includes('document')) return 'fa-file-word';
    if (mime.includes('text')) return 'fa-file-alt';
    return 'fa-file';
}
async function postData(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k, v));
    return fetchJSON(API, { method: 'POST', body: fd });
}
async function fetchJSON(url, opts = {}) {
    try {
        const r = await fetch(url, opts);
        return await r.json();
    } catch(e) { return { success: false }; }
}
})();
</script>
