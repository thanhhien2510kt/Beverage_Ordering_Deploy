/**
 * MeowBot Chat Widget - JavaScript
 * Handles: toggle, send message, receive reply, add-to-cart actions
 */
(function () {
    'use strict';

    const PROXY_URL = window.MEOWBOT_PROXY_URL || 'api/chatbot/proxy.php';
    const STORAGE_KEY = 'meowbot_session';
    const HISTORY_KEY = 'meowbot_history';

    // ── State ──────────────────────────────────────────────
    let sessionId = sessionStorage.getItem(STORAGE_KEY) || null;
    let history   = JSON.parse(sessionStorage.getItem(HISTORY_KEY) || '[]');
    let isOpen    = false;
    let isLoading = false;

    // ── DOM refs ───────────────────────────────────────────
    const widget      = document.getElementById('meowbot-widget');
    const toggleBtn   = document.getElementById('meowbot-toggle');
    const closeBtn    = document.getElementById('meowbot-close');
    const panel       = document.getElementById('meowbot-panel');
    const messages    = document.getElementById('meowbot-messages');
    const input       = document.getElementById('meowbot-input');
    const sendBtn     = document.getElementById('meowbot-send');
    const suggestions = document.getElementById('meowbot-suggestions');
    const unreadBadge = document.getElementById('meowbot-unread');
    const iconOpen    = toggleBtn.querySelector('.meowbot-icon-open');
    const iconClose   = toggleBtn.querySelector('.meowbot-icon-close');

    if (!widget) return; // widget not in DOM

    // ── Toggle panel ───────────────────────────────────────
    function openPanel() {
        isOpen = true;
        panel.style.display = 'flex';
        iconOpen.style.display  = 'none';
        iconClose.style.display = 'flex';
        unreadBadge.style.display = 'none';
        setTimeout(() => input.focus(), 100);
        scrollToBottom();
    }

    function closePanel() {
        isOpen = false;
        panel.style.display = 'none';
        iconOpen.style.display  = 'flex';
        iconClose.style.display = 'none';
    }

    toggleBtn.addEventListener('click', () => isOpen ? closePanel() : openPanel());
    closeBtn.addEventListener('click', closePanel);

    // ── Input auto-resize + send enable ───────────────────
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        sendBtn.disabled = this.value.trim().length === 0;
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendBtn.disabled) sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    // Suggestion chips
    suggestions.querySelectorAll('.meowbot-suggestion-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const msg = this.dataset.msg;
            if (msg) {
                input.value = msg;
                sendBtn.disabled = false;
                sendMessage();
                suggestions.style.display = 'none';
            }
        });
    });

    // ── Send message ───────────────────────────────────────
    async function sendMessage() {
        const text = input.value.trim();
        if (!text || isLoading) return;

        // Clear input
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
        suggestions.style.display = 'none';

        // Append user bubble
        appendMessage('user', text);
        history.push({ role: 'user', content: text });

        // Show typing
        const typingEl = appendTyping();
        isLoading = true;

        try {
            const response = await fetch(PROXY_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: text,
                    session_id: sessionId,
                    history: history.slice(-10)   // send last 10 turns
                })
            });

            const data = await response.json();

            // Persist session id
            if (data.session_id) {
                sessionId = data.session_id;
                sessionStorage.setItem(STORAGE_KEY, sessionId);
            }

            const reply = data.reply || 'Xin lỗi, mình không hiểu yêu cầu này. Bạn thử lại nhé!';
            typingEl.remove();
            appendMessage('bot', reply);

            history.push({ role: 'assistant', content: reply });
            sessionStorage.setItem(HISTORY_KEY, JSON.stringify(history.slice(-20)));

            // Process special actions (add_to_cart etc.)
            if (data.actions && data.actions.length > 0) {
                handleActions(data.actions);
            }

            // Also parse inline action payload from reply text
            parseInlineActions(reply);

        } catch (err) {
            typingEl.remove();
            appendMessage('bot', '😔 Mình đang gặp sự cố kết nối. Vui lòng thử lại sau nhé!', true);
        } finally {
            isLoading = false;
        }
    }

    // ── DOM helpers ────────────────────────────────────────
    function appendMessage(role, text, isError = false) {
        const div = document.createElement('div');
        div.className = `meowbot-msg meowbot-msg--${role === 'user' ? 'user' : 'bot'}`;

        const bubble = document.createElement('div');
        bubble.className = 'meowbot-bubble';
        if (isError) bubble.style.background = '#fee2e2';

        // Render markdown-like: **bold**, bullet lines
        bubble.innerHTML = formatText(text);

        div.appendChild(bubble);
        messages.appendChild(div);
        scrollToBottom();
        return div;
    }

    function appendTyping() {
        const div = document.createElement('div');
        div.className = 'meowbot-msg meowbot-msg--bot meowbot-typing';
        div.innerHTML = `<div class="meowbot-bubble">
            <span class="meowbot-typing-dot"></span>
            <span class="meowbot-typing-dot"></span>
            <span class="meowbot-typing-dot"></span>
        </div>`;
        messages.appendChild(div);
        scrollToBottom();
        return div;
    }

    function scrollToBottom() {
        setTimeout(() => { messages.scrollTop = messages.scrollHeight; }, 50);
    }

    function formatText(text) {
        // Strip __ACTION_PAYLOAD__ lines from display
        text = text.replace(/__ACTION_PAYLOAD__:.*$/gm, '').trim();
        // **bold**
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Newlines
        text = text.replace(/\n/g, '<br>');
        return text;
    }

    // ── Handle add-to-cart action ──────────────────────────
    function parseInlineActions(replyText) {
        const match = replyText.match(/__ACTION_PAYLOAD__:\s*(\{.*\})/);
        if (!match) return;
        try {
            const payload = JSON.parse(match[1]);
            if (payload.__action === 'add_to_cart') {
                executeAddToCart(payload);
            }
        } catch (e) { /* ignore */ }
    }

    function handleActions(actions) {
        actions.forEach(action => {
            if (action.type === 'add_to_cart') {
                executeAddToCart(action.data);
            }
        });
    }

    function executeAddToCart(data) {
        // Call existing PHP cart API
        const formData = new FormData();
        formData.append('product_id', data.product_id);
        formData.append('quantity', data.quantity || 1);
        formData.append('base_price', data.base_price || 0);
        formData.append('total_price', data.total_price || 0);
        formData.append('options', data.options || '[]');

        // Determine base path from proxy URL
        const basePath = PROXY_URL.replace('api/chatbot/proxy.php', '');

        fetch(basePath + 'api/cart/add.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Update cart count badge in header
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) cartCount.textContent = res.cart_count;
                // Show add-to-cart success message
                appendMessage('bot', `🛒 Đã thêm vào giỏ hàng thành công! Giỏ hàng của bạn có **${res.cart_count}** sản phẩm.`);
            }
        })
        .catch(() => { /* silent fail - main reply already shown */ });
    }

    // ── Show unread badge when panel closed ────────────────
    const observer = new MutationObserver(() => {
        if (!isOpen) {
            unreadBadge.style.display = 'flex';
        }
    });
    observer.observe(messages, { childList: true });

})();
