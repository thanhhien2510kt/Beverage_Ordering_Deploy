<?php
$userRoleAdminBar = $_SESSION['user_role_name'] ?? '';
$showAdminBar = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && ($userRoleAdminBar === 'Staff' || $userRoleAdminBar === 'Admin');

if ($showAdminBar):
    // Ensure basePath is available
    $bp = isset($basePath) ? $basePath : '';
?>
<div class="admin-bar">
    <div class="admin-bar-container">
        <div class="admin-bar-left">
            <a href="<?php echo $bp; ?>pages/management/index.php" class="admin-bar-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span style="font-weight: 600;">Trang Quản Trị (Admin Panel)</span>
            </a>
            <a href="<?php echo $bp; ?>pages/management/order-management.php" class="admin-bar-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                </svg>
                Đơn hàng
            </a>
            <?php if ($userRoleAdminBar === 'Admin'): ?>
            <a href="<?php echo $bp; ?>pages/management/product-management.php" class="admin-bar-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="9" y1="3" x2="9" y2="21"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                </svg>
                Sản phẩm
            </a>
            <a href="<?php echo $bp; ?>pages/management/promotion-management.php" class="admin-bar-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                Khuyến mãi
            </a>
            <?php endif; ?>
        </div>
        <div class="admin-bar-right">
            <span class="admin-bar-text">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? $userRoleAdminBar); ?></strong> (<?php echo htmlspecialchars($userRoleAdminBar); ?>)</span>
            <a href="<?php echo $bp; ?>index.php" class="admin-bar-item" style="margin-left: 15px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                </svg>
                Về cửa hàng
            </a>
        </div>
    </div>
</div>

<style>
body {
    padding-top: 32px !important; 
}
.main-header {
    top: 32px !important;
}
.admin-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 32px;
    background-color: #1e1e1e;
    color: #fff;
    z-index: 999999;
    font-size: 13px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.admin-bar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 100%;
    padding: 0 15px;
    max-width: 100%;
}
.admin-bar-left, .admin-bar-right {
    display: flex;
    align-items: center;
    height: 100%;
}
.admin-bar-item {
    color: #e0e0e0;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 12px;
    height: 100%;
    transition: all 0.2s;
    border-right: 1px solid #333;
}
.admin-bar-left .admin-bar-item:first-child {
    border-left: 1px solid #333;
    background-color: #2a2a2a;
}
.admin-bar-item:hover {
    background-color: #333;
    color: #4CAF50;
}
.admin-bar-text {
    color: #aaa;
    padding: 0 10px;
}
.admin-bar-text strong {
    color: #fff;
}
</style>
<?php endif; ?>
