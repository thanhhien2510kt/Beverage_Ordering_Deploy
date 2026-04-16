<?php
header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => ''];
try {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        throw new Exception('Giỏ hàng trống');
    }

    $itemIndex = isset($_POST['item_index']) ? (int)$_POST['item_index'] : -1;
    
    if ($itemIndex < 0 || $itemIndex >= count($_SESSION['cart'])) {
        throw new Exception('Mã sản phẩm không hợp lệ');
    }

    if (isset($_POST['quantity'])) {
        $quantity = max(1, (int)$_POST['quantity']);
        $_SESSION['cart'][$itemIndex]['quantity'] = $quantity;
        
        $basePrice = $_SESSION['cart'][$itemIndex]['base_price'] ?? 0;
        $options = $_SESSION['cart'][$itemIndex]['options'] ?? [];
        $optionPrice = 0;
        foreach ($options as $option) {
            $optionPrice += isset($option['price']) ? (float)$option['price'] : 0;
        }
        $_SESSION['cart'][$itemIndex]['total_price'] = ($basePrice + $optionPrice) * $quantity;
    }

    if (isset($_POST['note'])) {
        $_SESSION['cart'][$itemIndex]['note'] = trim($_POST['note']);
    }

    if (isset($_SESSION['user']) && isset($_SESSION['user']['mauser'])) {
        require_once '../../functions.php';
        $userId = $_SESSION['user']['mauser'];
        $storeId = isset($_SESSION['selected_store']) ? (int)$_SESSION['selected_store'] : 1;
        
        $dbSaved = saveCartToDB($userId, $storeId);
        if (!$dbSaved) {
            error_log("Warning: Failed to save cart to database for user " . $userId);
        }
    }

    $cartCount = 0;
    $totalAmount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
        $totalAmount += $item['total_price'] ?? 0;
    }

    $response = [
        'success' => true,
        'message' => 'Đã cập nhật giỏ hàng',
        'cart_count' => $cartCount,
        'total_amount' => $totalAmount,
        'item' => $_SESSION['cart'][$itemIndex]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
