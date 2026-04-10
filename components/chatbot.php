<?php
/**
 * Chatbot Widget Component
 * Include vào cuối body của header.php
 */
?>
<!-- ===== MeowBot Chat Widget ===== -->
<div id="meowbot-widget" class="meowbot-widget">

    <!-- Floating button -->
    <button id="meowbot-toggle" class="meowbot-toggle" aria-label="Mở chatbot MeowBot" title="Chat với MeowBot 🐱">
        <span class="meowbot-toggle-icon meowbot-icon-open">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </span>
        <span class="meowbot-toggle-icon meowbot-icon-close" style="display:none;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </span>
        <span class="meowbot-unread-badge" id="meowbot-unread" style="display:none;">1</span>
    </button>

    <!-- Chat panel -->
    <div id="meowbot-panel" class="meowbot-panel" style="display:none;" aria-live="polite">

        <!-- Header -->
        <div class="meowbot-header">
            <div class="meowbot-header-info">
                <div class="meowbot-avatar">🐱</div>
                <div>
                    <div class="meowbot-name">MeowBot</div>
                    <div class="meowbot-status"><span class="meowbot-dot"></span> Trực tuyến</div>
                </div>
            </div>
            <button class="meowbot-close-btn" id="meowbot-close" aria-label="Đóng chatbot">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Messages -->
        <div class="meowbot-messages" id="meowbot-messages">
            <!-- Welcome message -->
            <div class="meowbot-msg meowbot-msg--bot">
                <div class="meowbot-bubble">
                    Xin chào! Mình là <strong>MeowBot</strong> 🐱<br>
                    Mình có thể giúp bạn:
                    <ul style="margin:8px 0 0 16px; padding:0;">
                        <li>🍵 Tìm & tư vấn đồ uống</li>
                        <li>🛒 Thêm vào giỏ hàng</li>
                        <li>📦 Tra cứu đơn hàng</li>
                        <li>💬 Hỗ trợ khiếu nại</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div class="meowbot-suggestions" id="meowbot-suggestions">
            <button class="meowbot-suggestion-btn" data-msg="Trà sữa ngon nhất là gì?">🍵 Trà sữa ngon nhất</button>
            <button class="meowbot-suggestion-btn" data-msg="Xem menu cà phê">☕ Menu cà phê</button>
            <button class="meowbot-suggestion-btn" data-msg="Kiểm tra đơn hàng của tôi">📦 Kiểm tra đơn hàng</button>
        </div>

        <!-- Input -->
        <div class="meowbot-input-area">
            <textarea
                id="meowbot-input"
                class="meowbot-input"
                placeholder="Nhập tin nhắn..."
                rows="1"
                aria-label="Nhập tin nhắn cho MeowBot"
            ></textarea>
            <button id="meowbot-send" class="meowbot-send-btn" aria-label="Gửi tin nhắn" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>

    </div><!-- /panel -->
</div><!-- /widget -->

<!-- Chatbot JS & CSS -->
<link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/chatbot.css">
<script>
    window.MEOWBOT_PROXY_URL = '<?php echo $basePath; ?>api/chatbot/proxy.php';
    window.MEOWBOT_HISTORY_URL = '<?php echo $basePath; ?>api/chatbot/get_history.php';
</script>
<script src="<?php echo $basePath; ?>assets/js/chatbot.js" defer></script>
