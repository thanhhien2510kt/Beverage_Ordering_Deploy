<?php
/**
 * Add to Cart API
 * Thêm sản phẩm vào giỏ hàng
 * Tạm thời sử dụng session, sau sẽ tích hợp với database
 */

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $optionsJson = isset($_POST['options']) ? $_POST['options'] : '[]';
    $options = json_decode($optionsJson, true) ?: [];
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $basePrice = isset($_POST['base_price']) ? (float)$_POST['base_price'] : 0;
    $totalPrice = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;
    $referencePrice = (isset($_POST['reference_price']) && $_POST['reference_price'] !== '') ? (float)$_POST['reference_price'] : null;

    if (!$productId) {
        throw new Exception('Mã sản phẩm là bắt buộc');
    }

    // Get product from database to validate
    $product = getProductById($productId);
    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại');
    }

    // Enrich options with full information from database
    $enrichedOptions = enrichCartOptions($options);

    // Create cart item (base_price = GiaNiemYet, reference_price = GiaCoBan for strikethrough)
    $cartItem = [
        'product_id' => $productId,
        'product_name' => $product['TenSP'],
        'product_image' => $product['HinhAnh'] ?? 'assets/img/products/product_one.png',
        'quantity' => $quantity,
        'base_price' => $basePrice,
        'total_price' => $totalPrice,
        'reference_price' => $referencePrice,
        'options' => $enrichedOptions,
        'note' => $note,
        'added_at' => date('Y-m-d H:i:s')
    ];

    // Add to cart (for now, just append - later can merge same items)
    $_SESSION['cart'][] = $cartItem;

    // If user is logged in, save cart to database
    if (isset($_SESSION['user']) && isset($_SESSION['user']['MaUser'])) {
        $userId = $_SESSION['user']['MaUser'];
        $storeId = isset($_SESSION['selected_store']) ? (int)$_SESSION['selected_store'] : 1;
        
        // Save to database (but don't fail the request if it fails)
        $dbSaved = saveCartToDB($userId, $storeId);
        if (!$dbSaved) {
            error_log("Warning: Failed to save cart to database for user " . $userId);
        }
    }

    // Count total items
    $cartCount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }

    $response = [
        'success' => true,
        'message' => 'Đã thêm vào giỏ hàng',
        'cart_count' => $cartCount,
        'item' => $cartItem
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
