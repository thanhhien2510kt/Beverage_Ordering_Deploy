<?php
$currentScript = basename($_SERVER['PHP_SELF']);
$isAdminSidebar = ($userRole === 'Admin');
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2 style="margin: 0; font-size: 1.2rem; color: var(--primary-green);">Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li class="<?php echo $currentScript == 'order-management.php' ? 'active' : ''; ?>">
            <a href="order-management.php">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                </svg>
                Quản lý đơn hàng
            </a>
        </li>
        <?php if ($isAdminSidebar): ?>
        <li class="<?php echo $currentScript == 'product-management.php' ? 'active' : ''; ?>">
            <a href="product-management.php">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="9" y1="3" x2="9" y2="21"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                </svg>
                Quản lý sản phẩm
            </a>
        </li>
        <li class="<?php echo $currentScript == 'promotion-management.php' ? 'active' : ''; ?>">
            <a href="promotion-management.php">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                Quản lý khuyến mãi
            </a>
        </li>
        <li class="<?php echo $currentScript == 'user-management.php' ? 'active' : ''; ?>">
            <a href="user-management.php">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Quản lý người dùng
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>

<style>
.admin-sidebar {
    width: 250px;
    background-color: var(--white);
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}
.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar-menu li {
    border-bottom: 1px solid #f0f0f0;
}
.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: var(--text-dark);
    text-decoration: none;
    gap: 12px;
    transition: all 0.2s ease;
}
.sidebar-menu li a:hover, .sidebar-menu li.active a {
    background-color: var(--light-green);
    color: var(--primary-green);
}
.sidebar-menu li.active a {
    font-weight: 600;
    border-left: 4px solid var(--primary-green);
}
</style>
