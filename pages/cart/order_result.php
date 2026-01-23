<?php

require_once '../../functions.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}


$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    header('Location: index.php');
    exit;
}


$user = getCurrentUser();
$userId = $user['id'];


$pdo = getDBConnection();
$sql = "SELECT o.*, s.TenStore, s.DiaChi as StoreAddress, s.DienThoai as StorePhone
        FROM Orders o
        INNER JOIN Store s ON o.MaStore = s.MaStore
        WHERE o.MaOrder = ? AND o.MaUser = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();


if (!$order) {

    $basePath = '../../';
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đơn hàng không tồn tại - MeowTea Fresh</title>
        <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
        <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/cart.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body>
        <?php include '../../components/header.php'; ?>

        <main class="order-result-page">
            <div class="container">
                <div class="order-not-found">
                    <div class="not-found-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <h1 class="not-found-title">Đơn hàng không tồn tại</h1>
                    <p class="not-found-message">
                        Không tìm thấy đơn hàng với mã <strong>#<?php echo e('MTF' . str_pad($orderId, 5, '0', STR_PAD_LEFT)); ?></strong>
                    </p>
                    <p class="not-found-description">
                        Đơn hàng có thể đã bị xóa hoặc bạn không có quyền truy cập đơn hàng này.
                    </p>
                    <div class="not-found-actions">
                        <a href="index.php" class="btn-back-to-cart">Quay lại giỏ hàng</a>
                        <a href="../menu/index.php" class="btn-continue-shopping">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        </main>

        <?php 
            $href = "#top";
            include '../../components/back-to-top.php';
        ?>

        <?php include '../../components/footer.php'; ?>

        <?php include '../../components/snack-bar.php'; ?>

        <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
        <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
        <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
    </body>
    </html>
    <?php
    exit;
}


$paymentMethodId = $_SESSION['order_payment_' . $orderId] ?? null;
$paymentMethodName = 'Ví Zalo Pay'; // Default
if ($paymentMethodId) {
    $sql = "SELECT TenPayment FROM Payment_Method WHERE MaPayment = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$paymentMethodId]);
    $pm = $stmt->fetch();
    if ($pm) {
        $paymentMethodName = $pm['TenPayment'];
    }
}
$order['PaymentMethod'] = $paymentMethodName;


$sql = "SELECT oi.*, sp.TenSP, sp.HinhAnh
        FROM Order_Item oi
        INNER JOIN SanPham sp ON oi.MaSP = sp.MaSP
        WHERE oi.MaOrder = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();


foreach ($orderItems as &$item) {
    $sql = "SELECT oio.*, ov.TenGiaTri, og.TenNhom
            FROM Order_Item_Option oio
            INNER JOIN Option_Value ov ON oio.MaOptionValue = ov.MaOptionValue
            INNER JOIN Option_Group og ON ov.MaOptionGroup = og.MaOptionGroup
            WHERE oio.MaOrderItem = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item['MaOrderItem']]);
    $item['options'] = $stmt->fetchAll();
}
unset($item); 


$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += ($item['GiaNiemYet'] * $item['SoLuong']);
    if (isset($item['options'])) {
        foreach ($item['options'] as $option) {
            $subtotal += ($option['GiaThem'] * $item['SoLuong']);
        }
    }
}

$shippingFee = $order['PhiVanChuyen'] ?? 0;
$totalAmount = $order['TongTien'] ?? 0;
$promotionDiscount = $order['GiamGia'] ?? 0;


$orderCode = 'MTF' . str_pad($orderId, 5, '0', STR_PAD_LEFT);


$estimatedDelivery = date('H:i d/m/Y', strtotime('+1 hour'));


$orderStatus = $order['TrangThai'] ?? 'Payment_Received';
$orderDate = $order['NgayTao'] ?? date('Y-m-d H:i:s');


$step1Completed = in_array($orderStatus, ['Payment_Received', 'Pending', 'Processing', 'Order_Received', 'Delivering', 'Completed']);
$step2Completed = in_array($orderStatus, ['Processing', 'Order_Received', 'Delivering', 'Completed']);
$step3Completed = in_array($orderStatus, ['Delivering', 'Completed']);
$step4Completed = ($orderStatus === 'Completed');


$orderTime = date('H:i', strtotime($orderDate));


$basePath = '../../';
$iconPath = $basePath . 'assets/img/cart/order_result/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/cart.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <main class="order-result-page">
        <div class="container">
            <!-- Success Message -->
            <div class="order-success-header">
                <h1 class="success-title">Đặt Hàng Thành Công</h1>
                <p class="order-code-label">Mã đơn hàng của bạn</p>
                <div class="order-code-badge">#<?php echo e($orderCode); ?></div>
                <p class="email-notice">
                    Vui lòng kiểm tra hộp thư đến trong email của bạn để xem thông tin chi tiết đơn hàng
                </p>
                <p class="email-address"><?php echo e($user['email'] ?? 'email@gmail.com'); ?></p>
            </div>

            <!-- Order Progress Tracker -->
            <div class="order-progress">
                <!-- Step 1: Đã nhận thanh toán -->
                <div class="progress-step <?php echo $step1Completed ? 'completed' : ''; ?>">
                    <div class="step-icon">
                        <?php if ($step1Completed): ?>
                            <img src="<?php echo $iconPath; ?>paid.png" alt="Đã nhận thanh toán">
                        <?php else: ?>
                            <img src="<?php echo $iconPath; ?>green/order_accepted.png" alt="Đã nhận thanh toán">
                        <?php endif; ?>
                    </div>
                    <div class="step-info">
                        <p class="step-title">Đã nhận thanh toán</p>
                        <p class="step-time"><?php echo $step1Completed ? e($orderTime) : '-'; ?></p>
                    </div>
                </div>
                <div class="progress-line <?php echo $step1Completed ? 'active' : ''; ?>"></div>
                
                <!-- Step 2: Đã nhận đơn -->
                <div class="progress-step <?php echo $step2Completed ? 'completed' : ''; ?>">
                    <div class="step-icon">
                        <img src="<?php echo $iconPath . ($step2Completed ? 'white/order_accepted.png' : 'green/order_accepted.png'); ?>" 
                             alt="Đã nhận đơn"
                             onerror="this.src='<?php echo $iconPath . ($step2Completed ? 'white/icon_pick_up.png' : 'green/order_accepted.png'); ?>'">
                    </div>
                    <div class="step-info">
                        <p class="step-title">Đã nhận đơn</p>
                        <p class="step-time"><?php echo $step2Completed ? e($orderTime) : '-'; ?></p>
                    </div>
                </div>
                <div class="progress-line <?php echo $step2Completed ? 'active' : ''; ?>"></div>
                
                <!-- Step 3: Đang giao hàng -->
                <div class="progress-step <?php echo $step3Completed ? 'completed' : ''; ?>">
                    <div class="step-icon">
                        <img src="<?php echo $iconPath . ($step3Completed ? 'white/delivered.png' : 'green/delivered.png'); ?>" 
                             alt="Đang giao hàng"
                             onerror="this.src='<?php echo $iconPath . ($step3Completed ? 'white/icon_pick_up.png' : 'green/delivered.png'); ?>'">
                    </div>
                    <div class="step-info">
                        <p class="step-title">Đang giao hàng</p>
                        <p class="step-time"><?php echo $step3Completed ? e($orderTime) : '-'; ?></p>
                    </div>
                </div>
                <div class="progress-line <?php echo $step3Completed ? 'active' : ''; ?>"></div>
                
                <!-- Step 4: Hoàn thành -->
                <div class="progress-step <?php echo $step4Completed ? 'completed' : ''; ?>">
                    <div class="step-icon">
                        <img src="<?php echo $iconPath . ($step4Completed ? 'white/order_finished.png' : 'green/order_finished.png'); ?>" 
                             alt="Hoàn thành">
                    </div>
                    <div class="step-info">
                        <p class="step-title">Hoàn thành</p>
                        <p class="step-time"><?php echo $step4Completed ? e($orderTime) : '-'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary-section">
                <h2 class="summary-section-title">Tóm tắt đơn hàng</h2>
                <div class="summary-details">
                    <div class="summary-detail-row">
                        <span class="detail-label">Thời gian giao hàng dự kiến</span>
                        <span class="detail-value"><?php echo e($estimatedDelivery); ?></span>
                    </div>
                    <div class="summary-detail-row">
                        <span class="detail-label">Phương thức thanh toán</span>
                        <span class="detail-value"><?php echo e($order['PaymentMethod'] ?? 'Ví Zalo Pay'); ?></span>
                    </div>
                    <div class="summary-detail-row">
                        <span class="detail-label">Thành tiền</span>
                        <span class="detail-value"><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    <div class="summary-detail-row">
                        <span class="detail-label">Phí vận chuyển</span>
                        <span class="detail-value"><?php echo formatCurrency($shippingFee); ?></span>
                    </div>
                    <?php if ($promotionDiscount > 0): ?>
                    <div class="summary-detail-row">
                        <span class="detail-label">Khuyến mãi</span>
                        <span class="detail-value" style="color: #e74c3c;">-<?php echo formatCurrency($promotionDiscount); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-detail-row total-row">
                        <span class="detail-label">Số tiền thanh toán</span>
                        <span class="detail-value total-value"><?php echo formatCurrency($totalAmount); ?></span>
                    </div>
                </div>
            </div>

            <!-- Support Contact -->
            <div class="support-section">
                <p class="support-text">
                    Bạn cần hỗ trợ về đơn hàng? Vui lòng liên hệ:
                </p>
                <p class="support-phone">1900 1111 (miễn phí)</p>
            </div>
        </div>
    </main>

    <?php 
        $href = "#top";
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/snack-bar.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
</body>
</html>
