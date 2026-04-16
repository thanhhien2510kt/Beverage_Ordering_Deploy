<?php

require_once '../../functions.php';


if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}


if (!isLoggedIn()) {
    header('Location: ../auth/login.php?redirect=cart/checkout.php');
    exit;
}


if (isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);
    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        header('Location: ../../index.php');
        exit;
    }
}


$cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cartItems)) {
    header('Location: index.php');
    exit;
}


$user = getCurrentUser();
$userFullName = getFullName($user['ho'] ?? '', $user['ten'] ?? '');
$userPhone = $user['phone'] ?? '';
$userEmail = $user['email'] ?? '';
$userAddress = $_SESSION['user_dia_chi'] ?? '';


$stores = getStores();
$paymentMethods = getPaymentMethods();


$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += isset($item['total_price']) ? (float)$item['total_price'] : 0;
}
$shippingFee = 30000; // Default shipping fee
$promotionDiscount = 0;
$totalAmount = $subtotal + $shippingFee - $promotionDiscount;


$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/cart.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <main class="checkout-page">
        <div class="container">
            <h1 class="checkout-title">Xác nhận đơn hàng</h1>

            <div class="checkout-layout">
                <!-- Left Column: Order Details -->
                <div class="checkout-left">
                    <!-- Delivery Information -->
                    <section class="checkout-section">
                        <h2 class="section-title">Thông tin nhận hàng</h2>
                        <div class="delivery-info">
                            <div class="info-row">
                                <span class="info-label">Họ tên:</span>
                                <span class="info-value"><?php echo e($userFullName); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value"><?php echo e($userPhone); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Địa chỉ giao hàng:</span>
                                <span class="info-value" id="delivery-address-display"><?php echo e($userAddress ?: 'Chưa có địa chỉ'); ?></span>
                                <a href="#" class="change-address-link" id="change-address-btn"><?php echo $userAddress ? 'Đổi địa chỉ' : 'Thêm địa chỉ'; ?></a>
                            </div>
                            <div class="info-row address-row address-edit-row" id="address-edit-block" style="display: none;">
                                <div class="address-edit-inner">
                                    <textarea class="order-note-input" id="delivery-address-input" placeholder="Nhập địa chỉ giao hàng" maxlength="500" rows="3"><?php echo e($userAddress); ?></textarea>
                                    <div class="address-edit-actions">
                                        <button type="button" class="btn-save-address" id="btn-save-address">Lưu</button>
                                        <button type="button" class="btn-cancel-address" id="btn-cancel-address">Hủy</button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="delivery-address" value="<?php echo e($userAddress); ?>">
                        </div>
                    </section>

                    <!-- Store Selection -->
                    <section class="checkout-section">
                        <h2 class="section-title">Giao từ cửa hàng</h2>
                        <div class="store-selection-group">
                            <select class="store-select dropdown-select" id="province-select" name="province">
                                <option value="">Tỉnh/Thành phố</option>
                                <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                                <option value="Hà Nội">Hà Nội</option>
                                <option value="Đà Nẵng">Đà Nẵng</option>
                                <option value="Cần Thơ">Cần Thơ</option>
                            </select>
                            <select class="store-select dropdown-select" id="store-select" name="store_id" required disabled>
                                <option value="">Chọn cửa hàng</option>
                                <?php 

                                $provinceOptions = ['Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ'];
                                foreach ($stores as $store): 
                                    $address = $store['diachi'] ?? '';
                                    $matchedProvince = '';
                                    foreach ($provinceOptions as $provinceName) {
                                        if (mb_stripos($address, $provinceName) !== false) {
                                            $matchedProvince = $provinceName;
                                            break;
                                        }
                                    }
                                ?>
                                    <option value="<?php echo $store['mastore']; ?>" 
                                            data-phone="<?php echo e($store['dienthoai'] ?? ''); ?>"
                                            data-address="<?php echo e($store['diachi']); ?>"
                                            data-province="<?php echo e($matchedProvince); ?>">
                                        <?php echo e($store['tenstore']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="store-info" id="store-info" style="display: none;">
                            <p class="store-phone" id="store-phone"></p>
                            <p class="store-address" id="store-address"></p>
                        </div>
                    </section>

                    <!-- Order Notes -->
                    <section class="checkout-section">
                        <h2 class="section-title">Ghi chú đơn hàng</h2>
                        <textarea class="order-note-input" 
                                  id="order-note" 
                                  name="order_note" 
                                  placeholder="Nhập nội dung ghi chú cho đơn hàng (nếu có)" 
                                  maxlength="52"></textarea>
                        <span class="note-counter">0/52 ký tự</span>
                    </section>

                    <!-- VAT Invoice -->
                    <section class="checkout-section">
                        <label class="vat-checkbox-label">
                            <input type="checkbox" id="vat-invoice" name="vat_invoice">
                            <span>Tôi muốn xuất hóa đơn VAT</span>
                        </label>
                        <div class="vat-fields" id="vat-fields" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Email nhận hóa đơn *</label>
                                <input type="email" class="form-input" name="vat_email" placeholder="Nhập Email">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mã số thuế *</label>
                                <div class="form-group-inline">
                                    <input type="text" class="form-input" name="vat_tax_id" placeholder="Nhập Mã số thuế">
                                    <?php 
                                        $text = 'Tra cứu';
                                        $type = 'secondary';
                                        $class = 'btn-lookup';
                                        $width = 'auto';
                                        include '../../components/button.php';
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tên công ty *</label>
                                <input type="text" class="form-input" name="vat_company" placeholder="Nhập tên công ty">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Địa chỉ công ty *</label>
                                <input type="text" class="form-input" name="vat_address" placeholder="Nhập địa chỉ công ty">
                            </div>
                        </div>
                    </section>

                    <!-- Payment Methods -->
                    <section class="checkout-section">
                        <h2 class="section-title">Phương thức thanh toán</h2>
                        <div class="payment-methods">
                            <?php 
                            $paymentIcons = [
                                'Ví MoMo' => 'momo',
                                'Ví ZaloPay' => 'zalopay',
                                'Thẻ tín dụng' => 'card',
                                'Chuyển khoản ngân hàng' => 'bank',
                                'Thanh toán qua ATM' => 'atm',
                                'Ví điện tử VNPAY' => 'vnpay'
                            ];
                            foreach ($paymentMethods as $index => $method): 
                                $methodName = $method['tenpayment'];
                                $isChecked = $index === 0;
                            ?>
                                <label class="payment-method-option">
                                    <input type="radio" 
                                           name="payment_method" 
                                           value="<?php echo $method['mapayment']; ?>" 
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <span class="payment-method-name"><?php echo e($methodName); ?></span>
                                    <?php if (isset($paymentIcons[$methodName])): ?>
                                        <span class="payment-icon payment-icon-<?php echo $paymentIcons[$methodName]; ?>"></span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <!-- Right Column: Order Summary -->
                <div class="checkout-right">
                    <div class="order-summary-card">
                        <div class="summary-header">
                            <h2 class="summary-title">Các món đã chọn</h2>
                            <a href="../menu/index.php" class="add-item-link">Thêm món</a>
                        </div>

                        <div class="summary-items">
                            <?php foreach ($cartItems as $index => $item): 
                                $productImage = !empty($item['product_image']) ? $item['product_image'] : 'assets/img/products/product_one.png';
                                $productImage = getImagePath($productImage);
                                $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : 0;
                                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                                $itemTotal = isset($item['total_price']) ? (float)$item['total_price'] : $basePrice * $quantity;
                                $options = isset($item['options']) ? $item['options'] : [];

                                $pricePerUnit = $quantity > 0 ? ($itemTotal / $quantity) : $basePrice;
                            ?>
                                <div class="summary-item">
                                    <div class="summary-item-image">
                                        <img src="<?php echo e($basePath . $productImage); ?>" alt="<?php echo e($item['product_name']); ?>">
                                    </div>
                                    <div class="summary-item-info">
                                        <h3 class="summary-item-name"><?php echo e($item['product_name']); ?></h3>
                                        <?php if (!empty($options)): ?>
                                            <div class="summary-item-options">
                                                <?php foreach ($options as $option): ?>
                                                    <span class="option-tag-small">
                                                        <?php 
                                                        if (isset($option['group_name'])) {
                                                            echo e($option['group_name']) . ': ';
                                                        }
                                                        echo e($option['value_name'] ?? '');
                                                        ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="summary-item-price">
                                            <span class="current-price"><?php echo formatCurrency($pricePerUnit); ?></span>
                                            <?php 
                                            $refPrice = isset($item['reference_price']) ? (float)$item['reference_price'] : 0;
                                            if ($refPrice > $pricePerUnit): ?>
                                                <span class="old-price"><?php echo formatCurrency($refPrice); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Promotion Code -->
                        <div class="promotion-section">
                            <label class="promotion-label">Khuyến mãi</label>
                            <div class="promotion-input-wrapper">
                                <div class="promotion-input-container">
                                    <input type="text" 
                                           class="promotion-input" 
                                           id="promotion-code" 
                                           placeholder="Nhập mã khuyến mãi"
                                           autocomplete="off">
                                    <span class="promotion-clear-btn" id="promotion-clear-btn" style="display: none;">×</span>
                                </div>
                                <?php 
                                    $text = 'Áp dụng';
                                    $type = 'primary';
                                    $id = 'btn-apply-promotion';
                                    $class = 'btn-apply-promotion';
                                    $width = 'auto';
                                    include '../../components/button.php';
                                ?>
                            </div>
                            <div class="promotion-message" id="promotion-message"></div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="payment-summary">
                            <div class="summary-row">
                                <span class="summary-label">Tạm tính</span>
                                <span class="summary-value" id="subtotal"><?php echo formatCurrency($subtotal); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Phí vận chuyển</span>
                                <span class="summary-value" id="shipping-fee"><?php echo formatCurrency($shippingFee); ?></span>
                            </div>
                            <div class="summary-row promotion-row" id="promotion-row" style="display: none;">
                                <span class="summary-label">Khuyến mãi</span>
                                <span class="summary-value promotion-value" id="promotion-discount">-0₫</span>
                            </div>
                            <div class="summary-row total-row">
                                <span class="summary-label">Số tiền thanh toán</span>
                                <span class="summary-value total-value" id="total-amount"><?php echo formatCurrency($totalAmount); ?></span>
                            </div>
                        </div>

                        <!-- Terms Checkbox -->
                        <label class="terms-checkbox-label">
                            <input type="checkbox" id="agree-terms" required>
                            <span>Tôi đồng ý với những <a href="#" class="terms-link">điều khoản mua hàng</a> của MeowTea Fresh.</span>
                        </label>

                        <!-- Checkout Button -->
                        <?php 
                            $text = 'Thanh toán ngay';
                            $type = 'primary';
                            $id = 'pay-now-btn';
                            $class = 'btn-pay-now';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php 
        $href = "#top";
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/modal-box.php'; ?>
    <?php include '../../components/snack-bar.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/modal-box.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/checkout.js"></script>
</body>
</html>
