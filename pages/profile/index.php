<?php
/**
 * Profile Page
 * Trang thông tin tài khoản và đổi mật khẩu
 */

require_once '../../functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../../pages/auth/login.php');
    exit;
}

// Get user data from session
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';
$userHo = $_SESSION['user_ho'] ?? '';
$userTen = $_SESSION['user_ten'] ?? '';
$userName = $_SESSION['user_name'] ?? '';
$userGioiTinh = $_SESSION['user_gioi_tinh'] ?? null;
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
$userDiaChi = $_SESSION['user_dia_chi'] ?? '';
$userRole = $_SESSION['user_role_name'] ?? '';

// Check if user is customer (can edit address)
$isCustomer = (strtolower($userRole) === 'customer');

// Calculate base path
$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin tài khoản - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/profile.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="profile-layout">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-avatar-card">
                        <div class="profile-avatar">
                            <?php
                            $avatarImagePath = getAvatarImagePath($userGioiTinh, $basePath);
                            if (!empty($avatarImagePath)): ?>
                                <img src="<?php echo e($avatarImagePath); ?>" alt="<?php echo e($userName); ?>" class="avatar-image-large">
                            <?php else: ?>
                                <span class="avatar-initial-large"><?php echo e(getAvatarInitialFromName($userHo, $userTen)); ?></span>
                            <?php endif; ?>
                        </div>
                        <h2 class="profile-name"><?php echo e($userName); ?></h2>
                        <p class="profile-username">@<?php echo e($username); ?></p>
                        <p class="profile-role"><?php echo e($userRole); ?></p>
                    </div>

                    <nav class="profile-nav">
                        <a href="#info" class="profile-nav-item active" data-tab="info">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span>Thông tin cá nhân</span>
                        </a>
                        <a href="#password" class="profile-nav-item" data-tab="password">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Đổi mật khẩu</span>
                        </a>
                        <a href="<?php echo $basePath; ?>api/auth/logout.php" class="profile-nav-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <span>Đăng xuất</span>
                        </a>
                    </nav>
                </div>

                <!-- Profile Content -->
                <div class="profile-content">
                    <?php include 'profile-info.php'; ?>
                    <?php include 'profile-password.php'; ?>
                </div>
            </div>
        </div>
    </section>

    <?php 
        $bgType = 'light-green';
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/profile.js"></script>
</body>
</html>
