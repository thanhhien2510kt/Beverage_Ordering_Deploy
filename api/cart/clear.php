<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['cart'] = [];

echo json_encode([
    'success' => true,
    'message' => 'Đã xóa tất cả sản phẩm khỏi giỏ hàng',
    'cart_count' => 0
], JSON_UNESCAPED_UNICODE);
exit;
