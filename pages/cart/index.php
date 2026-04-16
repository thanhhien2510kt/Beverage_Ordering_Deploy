<?php

require_once '../../functions.php';


if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}


if (isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);
    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        header('Location: ../../index.php');
        exit;
    }
}


$cartItems = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartCount = count($cartItems);


$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += isset($item['total_price']) ? (float)$item['total_price'] : 0;
}


$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/cart.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <main class="cart-page">
        <div class="container">
            <h1 class="cart-title">Giỏ hàng</h1>

            <?php if (empty($cartItems)): ?>
                <div class="cart-empty">
                    <p>Giỏ hàng của bạn đang trống</p>
                    <?php 
                        $text = 'Tiếp tục mua sắm';
                        $type = 'primary';
                        $href = $basePath . 'pages/menu/index.php';
                        $width = 'auto';
                        include '../../components/button.php';
                    ?>
                </div>
            <?php else: ?>
                <!-- Cart Header -->
                <div class="cart-header">
                    <div class="cart-header-left">
                        <label class="checkbox-label">
                            <input type="checkbox" id="select-all-items" checked>
                            <span>Tất cả sản phẩm (<?php echo $cartCount; ?> sản phẩm)</span>
                        </label>
                    </div>
                    <span class="cart-header-col">Đơn giá</span>
                    <span class="cart-header-col">Số lượng</span>
                    <span class="cart-header-col">Thành tiền</span>
                    
                </div>

                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cartItems as $index => $item): 
                        $productImage = !empty($item['product_image']) ? $item['product_image'] : 'assets/img/products/product_one.png';
                        $productImage = getImagePath($productImage);
                        $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : 0;
                        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                        $itemTotal = isset($item['total_price']) ? (float)$item['total_price'] : $basePrice * $quantity;
                        $options = isset($item['options']) ? $item['options'] : [];
                        $note = isset($item['note']) ? $item['note'] : '';

                        $pricePerUnit = $quantity > 0 ? ($itemTotal / $quantity) : $basePrice;
                        

                        if (!empty($options) && !isset($options[0]['value_name'])) {
                            $options = enrichCartOptions($options);

                            $_SESSION['cart'][$index]['options'] = $options;
                        }
                    ?>
                        <div class="cart-item" data-item-index="<?php echo $index; ?>">
                            <div class="cart-item-main">
                                <div class="cart-item-left">
                                    <label class="checkbox-label">
                                        <input type="checkbox" class="item-checkbox" checked data-item-index="<?php echo $index; ?>">
                                    </label>
                                    <div class="cart-item-image">
                                        <img src="<?php echo e($basePath . $productImage); ?>" alt="<?php echo e($item['product_name']); ?>">
                                    </div>
                                    <div class="cart-item-info">
                                        <h3 class="cart-item-name"><?php echo e($item['product_name']); ?></h3>
                                        <?php if (!empty($options)): ?>
                                            <div class="cart-item-options">
                                                <?php foreach ($options as $option): 


                                                    $isAddon = false;
                                                    if (isset($option['ismultiple'])) {
                                                        $isAddon = (bool)$option['ismultiple'];
                                                    } elseif (isset($option['option_value_id'])) {


                                                        $isAddon = false;
                                                    }
                                                    
                                                    $valueName = $option['value_name'] ?? '';

                                                    if (empty($valueName) && isset($option['option_value_id'])) {


                                                        continue;
                                                    }
                                                ?>
                                                    <span class="option-tag">
                                                        <?php 

                                                        if ($isAddon && !empty($valueName)) {
                                                            echo '+ ' . e($valueName);
                                                        } else if (!empty($valueName)) {

                                                            echo e($valueName);
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="cart-item-price">
                                    <span class="price-value"><?php echo formatCurrency($pricePerUnit); ?></span>
                                </div>
                                <div class="cart-item-quantity">
                                    <button class="quantity-btn minus-btn" data-item-index="<?php echo $index; ?>">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $quantity; ?>" min="1" data-item-index="<?php echo $index; ?>">
                                    <button class="quantity-btn plus-btn" data-item-index="<?php echo $index; ?>">+</button>
                                </div>
                                <div class="cart-item-total">
                                    <span class="total-value"><?php echo formatCurrency($itemTotal); ?></span>
                                </div>
                                <div class="cart-item-delete">
                                    <button class="delete-btn" data-item-index="<?php echo $index; ?>" title="Xóa">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="cart-item-note">
                                <label class="note-label">Thêm ghi chú</label>
                                <textarea 
                                       class="note-input" 
                                       placeholder="Nhập nội dung ghi chú cho quán (nếu có)" 
                                       data-item-index="<?php echo $index; ?>"
                                       maxlength="52"><?php echo e($note); ?></textarea>
                                <span class="note-counter"><?php echo mb_strlen($note); ?>/52 ký tự</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Footer -->
                <div class="cart-footer">
                    <div class="cart-total">
                        <span class="total-label">Tổng tiền</span>
                        <span class="total-amount" id="cart-total-amount"><?php echo formatCurrency($totalAmount); ?></span>
                    </div>
                    <?php 
                        $text = 'Đặt hàng';
                        $type = 'primary';
                        $id = 'checkout-btn';
                        $class = 'btn-checkout';
                        $width = '200px';
                        include '../../components/button.php';
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../../components/back-to-top.php'; ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/modal-box.php'; ?>
    <?php include '../../components/snack-bar.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/modal-box.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/cart.js"></script>
</body>
</html>
