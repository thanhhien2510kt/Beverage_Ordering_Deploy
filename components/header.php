<?php
/**
 * Header Component
 * Reusable header với logo, navigation, cart, login
 */

require_once __DIR__ . '/../functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tính đường dẫn base từ vị trí file gọi component này
$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
$callerFile = isset($backtrace[0]['file']) ? $backtrace[0]['file'] : __FILE__;
$callerDir = dirname($callerFile);
$rootDir = dirname(__DIR__); // Root của project

// Normalize paths
$callerDir = realpath($callerDir);
$rootDir = realpath($rootDir);

// Tính số level cần lùi lại
if ($callerDir && $rootDir && strpos($callerDir, $rootDir) === 0) {
    $relativePath = str_replace($rootDir, '', $callerDir);
    $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
    $levels = $relativePath ? substr_count($relativePath, DIRECTORY_SEPARATOR) + 1 : 0;
    $basePath = $levels > 0 ? str_repeat('../', $levels) : '';
} else {
    $basePath = '';
}

// Tính đường dẫn index.php
$indexPath = $basePath . 'index.php';

// Xác định trang hiện tại để đánh dấu active
$currentScript = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['REQUEST_URI'];

// Xác định active link dựa trên path
$isHome = ($currentScript == 'index.php' && strpos($currentPath, '/pages/') === false);
$isMenu = strpos($currentPath, '/pages/menu/') !== false;
$isStores = strpos($currentPath, '/pages/stores/') !== false;
$isNews = strpos($currentPath, '/pages/news/') !== false;
$isCareer = strpos($currentPath, '/pages/career/') !== false;
$isAbout = strpos($currentPath, '/pages/about/') !== false;
$isManagement = strpos($currentPath, '/pages/management/') !== false;
$isPromotion = strpos($currentPath, '/pages/promotion/') !== false;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Check if user can use cart (only customers can use cart)
$canUseCart = true;
if ($isLoggedIn && isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);
    // Hide cart for admin and staff
    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        $canUseCart = false;
    }
}

// Ensure $_SESSION['user'] array exists for cart functions
if ($isLoggedIn && !isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'MaUser' => $_SESSION['user_id'] ?? null,
        'Username' => $_SESSION['username'] ?? '',
        'Ho' => $_SESSION['user_ho'] ?? '',
        'Ten' => $_SESSION['user_ten'] ?? '',
        'Email' => $_SESSION['user_email'] ?? '',
        'DienThoai' => $_SESSION['user_phone'] ?? '',
        'DiaChi' => $_SESSION['user_dia_chi'] ?? '',
        'MaRole' => $_SESSION['user_role'] ?? null,
        'TenRole' => $_SESSION['user_role_name'] ?? ''
    ];
}

// Load cart from database if logged in and not loaded yet (only for customers)
if ($isLoggedIn && $canUseCart && isset($_SESSION['user']['MaUser']) && !isset($_SESSION['cart_loaded_from_db'])) {
    $userId = $_SESSION['user']['MaUser'];
    $storeId = isset($_SESSION['selected_store']) ? (int)$_SESSION['selected_store'] : 1;
    
    // Load cart from database (only if session cart is empty)
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $cartLoaded = loadCartFromDB($userId, $storeId);
        if ($cartLoaded) {
            $_SESSION['cart_loaded_from_db'] = true;
        }
    } else {
        // If session cart has items, merge with DB (session takes priority)
        $cartMerged = mergeCartWithDB($userId, $storeId);
        if ($cartMerged) {
            $_SESSION['cart_loaded_from_db'] = true;
        }
    }
}

// Check user role for management access
$userRole = $isLoggedIn ? ($_SESSION['user_role_name'] ?? '') : '';
$showManagement = $isLoggedIn && ($userRole === 'Staff' || $userRole === 'Admin');
$isAdmin = $isLoggedIn && ($userRole === 'Admin');
$userHo = $isLoggedIn ? ($_SESSION['user_ho'] ?? '') : '';
$userTen = $isLoggedIn ? ($_SESSION['user_ten'] ?? '') : '';
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
$userGioiTinh = $isLoggedIn ? ($_SESSION['user_gioi_tinh'] ?? null) : null;
$avatarImagePath = $isLoggedIn ? getAvatarImagePath($userGioiTinh, $basePath) : '';
?>
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <!-- Logo -->
            <div class="logo">
                <a href="<?php echo $indexPath; ?>">
                    <img src="<?php echo $basePath; ?>assets/img/logo.png" alt="MeowTea Fresh" class="logo-img" style="width: 240px; height: 50px;">
                </a>
            </div>

            <!-- Navigation -->
            <nav class="main-nav">
                <ul class="nav-list">
                    <li>
                        <?php if ($isHome): ?>
                            <span class="nav-link active">Trang chủ</span>
                        <?php else: ?>
                            <a href="<?php echo $indexPath; ?>" class="nav-link">Trang chủ</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($isMenu): ?>
                            <span class="nav-link active">Menu</span>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>pages/menu/index.php" class="nav-link">Menu</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($isStores): ?>
                            <span class="nav-link active">Cửa hàng</span>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>pages/stores/index.php" class="nav-link">Cửa hàng</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($isNews): ?>
                            <span class="nav-link active">Tin tức</span>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>pages/news/index.php" class="nav-link">Tin tức</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($isCareer): ?>
                            <span class="nav-link active">Tuyển dụng</span>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>pages/career/index.php" class="nav-link">Tuyển dụng</a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($isAbout): ?>
                            <span class="nav-link active">Về MeowTea Fresh</span>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>pages/about/index.php" class="nav-link">Về MeowTea Fresh</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>

            <!-- User Actions -->
            <div class="header-actions">
                <?php if ($canUseCart): ?>
                <div class="cart-icon">
                    <a href="<?php echo $basePath; ?>pages/cart/index.php" class="cart-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 2L7 6H2v2h1l1 10h12l1-10h1V6h-5L15 2H9z"/>
                        </svg>
                        <span class="cart-text">Giỏ hàng</span>
                        <span class="cart-count">0</span>
                    </a>
                </div>
                <div class="separator">|</div>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <!-- User Info (when logged in) -->
                    <div class="user-info-wrapper">
                        <div class="user-avatar" title="<?php echo e($userName); ?>">
                            <?php if (!empty($avatarImagePath)): ?>
                                <img src="<?php echo e($avatarImagePath); ?>" alt="<?php echo e($userName); ?>" class="avatar-image">
                            <?php else: ?>
                                <span class="avatar-initial"><?php echo e(getAvatarInitialFromName($userHo, $userTen)); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="user-name"><?php echo e($userName); ?></span>
                        <div class="user-dropdown">
                            <button class="user-dropdown-toggle" aria-label="Menu người dùng">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>
                            <div class="user-dropdown-menu">
                                <a href="<?php echo $basePath; ?>pages/profile/index.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span>Thông tin tài khoản</span>
                                </a>
                                <?php if ($canUseCart): ?>
                                <a href="<?php echo $basePath; ?>pages/profile/orders.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                        <path d="M9 12h6M9 16h6"/>
                                    </svg>
                                    <span>Đơn hàng của tôi</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                <a href="<?php echo $basePath; ?>pages/management/product-management.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="9" y1="3" x2="9" y2="21"/>
                                        <line x1="3" y1="9" x2="21" y2="9"/>
                                    </svg>
                                    <span>Quản lý sản phẩm</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                <a href="<?php echo $basePath; ?>pages/management/promotion-management.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                    <span>Quản lý khuyến mãi</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($showManagement): ?>
                                <a href="<?php echo $basePath; ?>pages/management/order-management.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                        <path d="M9 12h6M9 16h6"/>
                                    </svg>
                                    <span>Quản lý đơn hàng</span>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo $basePath; ?>api/auth/logout.php" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                        <polyline points="16 17 21 12 16 7"/>
                                        <line x1="21" y1="12" x2="9" y2="12"/>
                                    </svg>
                                    <span>Đăng xuất</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login Link (when not logged in) -->
                    <div class="login-icon">
                        <a href="<?php echo $basePath; ?>pages/auth/login.php" class="login-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span class="login-text">Đăng nhập</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
