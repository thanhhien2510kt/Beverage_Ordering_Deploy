<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => ''];
try {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        throw new Exception('Cart is empty');
    }

    $itemIndex = isset($_POST['item_index']) ? (int)$_POST['item_index'] : -1;
    
    if ($itemIndex < 0 || $itemIndex >= count($_SESSION['cart'])) {
        throw new Exception('Invalid item index');
    }

    array_splice($_SESSION['cart'], $itemIndex, 1);

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
        'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
        'cart_count' => $cartCount,
        'total_amount' => $totalAmount
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
