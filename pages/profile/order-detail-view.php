<?php
/**
 * Order Detail View - Render order detail modal content
 */

require_once '../../functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isLoggedIn()) {
    echo '<p class="order-detail-error">Vui lòng đăng nhập để xem đơn hàng.</p>';
    exit;
}

// Get order ID from query string
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    echo '<p class="order-detail-error">Không tìm thấy đơn hàng.</p>';
    exit;
}

// Get user info
$user = getCurrentUser();
$userId = $user['id'];

// Get order from database
$pdo = getDBConnection();
$sql = "SELECT o.*, s.TenStore, s.DiaChi as StoreAddress, s.DienThoai as StorePhone
        FROM Orders o
        INNER JOIN Store s ON o.MaStore = s.MaStore
        WHERE o.MaOrder = ? AND o.MaUser = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

// Check if order exists
if (!$order) {
    echo '<p class="order-detail-error">Không tải được đơn hàng.</p>';
    exit;
}

// Get payment method
$paymentMethodId = $order['MaPayment'] ?? null;
$paymentMethodName = 'Tiền mặt';
if ($paymentMethodId) {
    $sql = "SELECT TenPayment FROM Payment_Method WHERE MaPayment = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$paymentMethodId]);
    $pm = $stmt->fetch();
    if ($pm) {
        $paymentMethodName = $pm['TenPayment'];
    }
}

// Get order items
$sql = "SELECT oi.*, sp.TenSP, sp.HinhAnh
        FROM Order_Item oi
        INNER JOIN SanPham sp ON oi.MaSP = sp.MaSP
        WHERE oi.MaOrder = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();

// Get order item options
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

// Calculate totals
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

// Generate order code
$orderCode = 'MTF' . str_pad($orderId, 5, '0', STR_PAD_LEFT);

// Get order status and format date
$orderStatus = $order['TrangThai'] ?? 'Payment_Received';
$orderDate = $order['NgayTao'] ?? date('Y-m-d H:i:s');
$orderDateFormatted = date('d/m/Y H:i:s', strtotime($orderDate));

// Get timeline timestamps
$thoiDiemNhanDon = $order['ThoiDiemNhanDon'] ?? null;
$thoiDiemGiaoHang = $order['ThoiDiemGiaoHang'] ?? null;
$thoiDiemNhanHang = $order['ThoiDiemNhanHang'] ?? null;
$thoiDiemHuyDon = $order['ThoiDiemHuyDon'] ?? null;

// Format timestamps
$step1Date = $orderDateFormatted; // Always show order creation date
$step2Date = $thoiDiemNhanDon ? date('d/m/Y H:i:s', strtotime($thoiDiemNhanDon)) : null;
$step3Date = $thoiDiemGiaoHang ? date('d/m/Y H:i:s', strtotime($thoiDiemGiaoHang)) : null;
$step4Date = $thoiDiemNhanHang ? date('d/m/Y H:i:s', strtotime($thoiDiemNhanHang)) : null;
$cancelDate = $thoiDiemHuyDon ? date('d/m/Y H:i:s', strtotime($thoiDiemHuyDon)) : $orderDateFormatted;

// Determine which steps are completed based on order status
$step1Completed = in_array($orderStatus, ['Payment_Received', 'Pending', 'Processing', 'Order_Received', 'Delivering', 'Completed']);
$step2Completed = in_array($orderStatus, ['Processing', 'Order_Received', 'Delivering', 'Completed']);
$step3Completed = in_array($orderStatus, ['Delivering', 'Completed']);
$step4Completed = ($orderStatus === 'Completed');

// Check if order is cancelled
$isCancelled = in_array($orderStatus, ['Cancelled', 'Store_Cancelled']);
if ($isCancelled) {
    $step1Completed = true;
    $step2Completed = false;
    $step3Completed = false;
    $step4Completed = false;
}

// Base path for assets
$basePath = '../../';
$iconPath = $basePath . 'assets/img/cart/order_result/';

// Get status text and class
function getStatusText($status) {
    $s = strtolower($status);
    if ($s === 'completed') return 'Hoàn thành';
    if ($s === 'cancelled' || $s === 'store_cancelled') return 'Đã hủy';
    if ($s === 'delivering') return 'Đang giao hàng';
    if ($s === 'processing' || $s === 'order_received') return 'Đã nhận đơn';
    if ($s === 'payment_received' || $s === 'pending') return 'Đã nhận thanh toán';
    return 'Đã nhận thanh toán';
}

function getStatusClass($status) {
    $s = strtolower($status);
    if ($s === 'completed') return 'completed';
    if ($s === 'cancelled' || $s === 'store_cancelled') return 'cancelled';
    if ($s === 'delivering') return 'delivering';
    if ($s === 'processing' || $s === 'order_received') return 'received';
    if ($s === 'payment_received' || $s === 'pending') return 'payment-received';
    return 'payment-received';
}

$statusText = getStatusText($orderStatus);
$statusClass = getStatusClass($orderStatus);
?>

<!-- Order Progress Section -->
<div class="order-progress-section">
    <h3 class="order-progress-section-title">Diễn biến đơn hàng</h3>
    
    <?php if ($isCancelled): ?>
        <div style="text-align: center; padding: 20px;">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" style="margin: 0 auto 15px;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <p style="color: #dc3545; font-weight: 600; font-size: 16px; margin: 0;">Đơn hàng đã bị hủy</p>
            <p style="color: #666; font-size: 14px; margin-top: 8px;"><?php echo e($cancelDate); ?></p>
        </div>
    <?php else: ?>
        <div class="order-progress-steps">
            <div class="order-progress-line"></div>
            <div class="order-progress-line-active" style="width: <?php 
                if ($step4Completed) echo '100%';
                elseif ($step3Completed) echo '66.67%';
                elseif ($step2Completed) echo '33.33%';
                elseif ($step1Completed) echo '0%';
                else echo '0%';
            ?>"></div>
            
            <!-- Step 1: Đã nhận thanh toán -->
            <div class="order-progress-step <?php echo $step1Completed ? 'completed' : ''; ?>">
                <div class="order-progress-icon">
                    <img src="<?php echo $iconPath . ($step1Completed ? 'paid.png' : 'green/order_accepted.png'); ?>" alt="Đã nhận thanh toán">
                </div>
                <div class="order-progress-label">Đã nhận thanh toán</div>
                <?php if ($step1Completed && $step1Date): ?>
                    <div class="order-progress-date"><?php echo e($step1Date); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Step 2: Đã nhận đơn -->
            <div class="order-progress-step <?php echo $step2Completed ? 'completed' : ''; ?>">
                <div class="order-progress-icon">
                    <img src="<?php echo $iconPath . ($step2Completed ? 'white/order_accepted.png' : 'green/order_accepted.png'); ?>" 
                         alt="Đã nhận đơn">
                </div>
                <div class="order-progress-label">Đã nhận đơn</div>
                <?php if ($step2Completed && $step2Date): ?>
                    <div class="order-progress-date"><?php echo e($step2Date); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Step 3: Đang giao hàng -->
            <div class="order-progress-step <?php echo $step3Completed ? 'completed' : ''; ?>">
                <div class="order-progress-icon">
                    <img src="<?php echo $iconPath . ($step3Completed ? 'white/delivered.png' : 'green/delivered.png'); ?>" 
                         alt="Đang giao hàng">
                </div>
                <div class="order-progress-label">Đang giao hàng</div>
                <?php if ($step3Completed && $step3Date): ?>
                    <div class="order-progress-date"><?php echo e($step3Date); ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Step 4: Hoàn thành -->
            <div class="order-progress-step <?php echo $step4Completed ? 'completed' : ''; ?>">
                <div class="order-progress-icon">
                    <img src="<?php echo $iconPath . ($step4Completed ? 'white/order_finished.png' : 'green/order_finished.png'); ?>" 
                         alt="Hoàn thành">
                </div>
                <div class="order-progress-label">Hoàn thành</div>
                <?php if ($step4Completed && $step4Date): ?>
                    <div class="order-progress-date"><?php echo e($step4Date); ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Thông tin đơn hàng -->
<div class="order-detail-section collapsible collapsed">
    <h3 class="order-detail-section-title">Thông tin đơn hàng</h3>
    <div class="order-detail-section-content">
        <div class="order-detail-info-grid">
            <div class="info-item">
                <span class="info-label">Mã đơn hàng:</span> 
                <span class="info-value"><?php echo e($orderCode); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Thời gian đặt hàng:</span> 
                <span class="info-value"><?php echo e($orderDateFormatted); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Trạng thái:</span> 
                <span class="order-detail-status status-<?php echo e($statusClass); ?>">
                    <?php echo e($statusText); ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Hình thức thanh toán:</span> 
                <span class="info-value"><?php echo e($paymentMethodName); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin nhận hàng -->
<div class="order-detail-section collapsible collapsed">
    <h3 class="order-detail-section-title">Thông tin nhận hàng</h3>
    <div class="order-detail-section-content">
        <div class="order-detail-info-grid">
            <div class="info-item">
                <span class="info-label">Họ và tên:</span> 
                <span class="info-value"><?php echo e($order['NguoiNhan'] ?? ''); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Số điện thoại:</span> 
                <span class="info-value"><?php echo e($order['DienThoaiGiao'] ?? ''); ?></span>
            </div>
            <div class="info-item full">
                <span class="info-label">Địa chỉ nhận hàng:</span> 
                <span class="info-value"><?php echo e($order['DiaChiGiao'] ?? ''); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Sản phẩm -->
<div class="order-detail-section collapsible collapsed">
    <h3 class="order-detail-section-title">Sản phẩm (<?php echo count($orderItems); ?>)</h3>
    <div class="order-detail-section-content">
        <div class="order-detail-products">
            <?php if (count($orderItems) > 0): ?>
                <?php foreach ($orderItems as $item): ?>
                    <?php
                    $img = $item['HinhAnh'] ?? 'assets/img/products/product_one.png';
                    if (strpos($img, 'http') !== 0) {
                        $img = $basePath . $img;
                    }
                    
                    // Calculate total price including options
                    $giaHienTai = $item['GiaNiemYet'];
                    $optionsText = [];
                    if (isset($item['options']) && count($item['options']) > 0) {
                        foreach ($item['options'] as $opt) {
                            $giaHienTai += $opt['GiaThem'];
                            $t = ($opt['GiaThem'] > 0) ? '+ ' . $opt['TenGiaTri'] : $opt['TenGiaTri'];
                            $optionsText[] = $t;
                        }
                    }
                    ?>
                    <div class="order-detail-product">
                        <div class="order-detail-product-img">
                            <img src="<?php echo e($img); ?>" alt="<?php echo e($item['TenSP']); ?>">
                        </div>
                        <div class="order-detail-product-info">
                            <p class="order-detail-product-name">
                                x<?php echo e($item['SoLuong']); ?> <?php echo e($item['TenSP']); ?>
                            </p>
                            <?php if (count($optionsText) > 0): ?>
                                <div class="order-detail-item-options">
                                    <?php echo e(implode(', ', $optionsText)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="order-detail-product-price">
                                <span class="order-detail-item-current-price">
                                    <?php echo formatCurrency($giaHienTai); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không có sản phẩm</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Số tiền thanh toán -->
<div class="order-detail-section">
    <h3 class="order-detail-section-title">Số tiền thanh toán</h3>
    <div class="order-detail-summary">
        <div class="order-detail-summary-row">
            <span class="info-label">Tạm tính:</span> 
            <span class="info-value"><?php echo formatCurrency($subtotal); ?></span>
        </div>
        <div class="order-detail-summary-row">
            <span class="info-label">Phí vận chuyển:</span> 
            <span class="info-value"><?php echo formatCurrency($shippingFee); ?></span>
        </div>
        <div class="order-detail-summary-row">
            <span class="info-label">Khuyến mãi:</span> 
            <span class="info-value"><?php echo ($promotionDiscount > 0 ? '-' : '') . formatCurrency($promotionDiscount); ?></span>
        </div>
        <div class="order-detail-summary-row total">
            <span class="info-label">Số tiền thanh toán:</span> 
            <span class="info-value"><?php echo formatCurrency($totalAmount); ?></span>
        </div>
    </div>
</div>
