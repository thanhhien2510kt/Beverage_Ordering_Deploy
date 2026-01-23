<?php
/**
 * My Orders Page
 * Trang đơn hàng của tôi (dành cho customer)
 * Layout giống trang quản lý đơn hàng
 */

require_once '../../functions.php';

// Check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

// Check if user is customer
$userRole = $_SESSION['user_role_name'] ?? '';
$userRoleLower = strtolower($userRole);
if ($userRoleLower !== 'customer') {
    header('Location: ../../index.php');
    exit;
}

$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/profile.css">
    <link rel="stylesheet" href="../../assets/css/management.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <section class="management-section" style="background-color: var(--light-green); min-height: calc(100vh - 200px); padding: 30px;">
        <div class="container">
            <div class="management-header">
                <div class="management-header-left">
                    <h1 class="management-title">Đơn hàng của tôi</h1>
                    <p class="management-subtitle">Xem lịch sử đơn hàng và trạng thái đơn hàng của bạn</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="orders-filters" style="margin-bottom: 30px;">
                <div class="orders-filters-left">
                    <div class="filter-group">
                        <label class="filter-label">Trạng thái:</label>
                        <select id="myOrderStatusFilter" class="filter-select">
                            <option value="">Tất cả</option>
                            <option value="payment_received">Đã nhận thanh toán</option>
                            <option value="processing">Đã nhận đơn</option>
                            <option value="delivering">Đang vận chuyển</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Hủy đơn</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Thời gian:</label>
                        <select id="myOrderDaysFilter" class="filter-select">
                            <option value="7">7 ngày</option>
                            <option value="30" selected>30 ngày</option>
                            <option value="90">90 ngày</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="orders-search-bar">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        id="myOrderSearchInput" 
                        class="search-input" 
                        placeholder="Tìm kiếm theo mã đơn hàng (VD: MTF00001)..."
                    >
                    <button type="button" id="clearMySearchBtn" class="clear-search-btn" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Orders Container -->
            <div class="orders-container" style="background: var(--white); border-radius: 30px; padding: 30px; box-shadow: var(--shadow);">
                <div id="myOrdersLoading" class="orders-loading" style="display: none;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="0.75"/>
                    </svg>
                    <p>Đang tải đơn hàng...</p>
                </div>
                <div id="myOrdersEmpty" class="orders-empty" style="display: none;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                    </svg>
                    <p>Bạn chưa có đơn hàng nào</p>
                    <?php 
                        $text = 'Đặt hàng ngay';
                        $type = 'primary';
                        $href = $basePath . 'pages/menu/index.php';
                        $width = '200px';
                        include '../../components/button.php';
                    ?>
                </div>
                <div id="myOrdersList" class="orders-list orders-list-cards"></div>
                <div id="myOrdersPagination" class="orders-pagination" style="display: none;"></div>
            </div>
        </div>
    </section>

    <!-- Order Detail Modal -->
    <div id="myOrderDetailModal" class="order-detail-modal" role="dialog" aria-labelledby="myOrderDetailTitle" aria-modal="true" style="display: none;">
        <div class="order-detail-overlay"></div>
        <div class="order-detail-content">
            <button type="button" class="order-detail-close" aria-label="Đóng">&times;</button>
            <div id="myOrderDetailBody"></div>
        </div>
    </div>

    <?php 
        $bgType = 'light-green';
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    <script src="../../assets/js/my-orders.js"></script>
</body>
</html>
