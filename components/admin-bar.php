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
                <span style="font-weight: 600;">Trang Quản Trị</span>
            </a>
        </div>
        <div class="admin-bar-right">
            <div class="user-info-wrapper admin-bar-profile">
                <div class="user-avatar" title="<?php echo htmlspecialchars($_SESSION['user_name'] ?? $userRoleAdminBar); ?>">
                    <span class="avatar-initial"><?php echo htmlspecialchars(mb_substr($_SESSION['user_name'] ?? $userRoleAdminBar, 0, 1)); ?></span>
                </div>
                <span class="user-name" style="color: #e8ede8;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? $userRoleAdminBar); ?></span>
                <div class="user-dropdown">
                    <button class="user-dropdown-toggle" style="color: #e8ede8;" aria-label="Menu người dùng">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div class="user-dropdown-menu">
                        <a href="<?php echo $bp; ?>pages/profile/index.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Thông tin tài khoản</span>
                        </a>
                        <a href="<?php echo $bp; ?>api/auth/logout.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            <span>Đăng xuất</span>
                        </a>
                    </div>
                </div>
            </div>
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
    background-color: #11331e;
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
    color: #e8ede8;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 12px;
    height: 100%;
    transition: all 0.2s;
    border-right: 1px solid #1a4d2e;
}
.admin-bar-left .admin-bar-item:first-child {
    border-left: 1px solid #1a4d2e;
    background-color: #1a4d2e;
}
.admin-bar-item:hover {
    background-color: #1a4d2e;
    color: #fff;
}
.admin-bar-text {
    color: #b3c5b9;
    padding: 0 10px;
}
.admin-bar-text strong {
    color: #fff;
}
.admin-bar-profile {
    display: flex;
    align-items: center;
    border-right: 1px solid #1a4d2e;
    padding-right: 15px;
    height: 100%;
}
.admin-bar-profile .user-name {
    margin-left: 8px;
    margin-right: 4px;
}
.admin-bar-profile .user-avatar {
    width: 24px;
    height: 24px;
}
.admin-bar-profile .avatar-initial {
    width: 24px;
    height: 24px;
    font-size: 12px;
}
.admin-bar-profile .user-dropdown-menu {
    top: 32px;
    right: auto;
}
.admin-bar-profile .dropdown-item {
    color: var(--text-dark);
}
</style>
<?php endif; ?>
