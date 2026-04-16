<?php
header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];
try {
    if (!isLoggedIn()) {
        throw new Exception('Vui lòng đăng nhập');
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

    if (!$orderId) {
        throw new Exception('Không tìm thấy mã đơn hàng');
    }

    $pdo = getDBConnection();
    $user = getCurrentUser();

    // Check order
    $stmt = $pdo->prepare("SELECT * FROM Orders WHERE MaOrder = ? AND MaUser = ? AND TrangThai = 'Pending'");
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại hoặc đã được xử lý');
    }

    $pdo->beginTransaction();

    // Get order items to restore cart
    $stmt = $pdo->prepare("SELECT * FROM Order_Item WHERE MaOrder = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cart = [];
    foreach ($orderItems as $item) {
        $productId = $item['masp'];
        $product = getProductById($productId);
        if (!$product)
            continue;
        $product = array_change_key_case($product, CASE_LOWER);

        $itemId = $item['maorderitem'];
        $stmtOpt = $pdo->prepare("SELECT * FROM Order_Item_Option WHERE MaOrderItem = ?");
        $stmtOpt->execute([$itemId]);
        $optionsRaw = $stmtOpt->fetchAll(PDO::FETCH_ASSOC);

        $optionList = [];
        $totalPrice = (float) $item['gianiemyet'];
        foreach ($optionsRaw as $opt) {
            $optValuePrice = (float) $opt['giathem'];
            $totalPrice += $optValuePrice;
            $optionList[] = [
                'option_value_id' => $opt['maoptionvalue'],
                'price' => $optValuePrice
            ];
        }
        $totalPrice *= (int) $item['soluong'];

        $enrichedOptions = enrichCartOptions($optionList);

        $cartItem = [
            'product_id' => $productId,
            'product_name' => $product['tensp'],
            'product_image' => $product['hinhanh'],
            'quantity' => (int) $item['soluong'],
            'base_price' => (float) $item['gianiemyet'],
            'total_price' => $totalPrice,
            'reference_price' => null,
            'options' => $enrichedOptions,
            'note' => '',
            'added_at' => date('Y-m-d H:i:s')
        ];
        $cart[] = $cartItem;
    }

    // Delete order options, items, and order itself
    $pdo->exec("DELETE FROM Order_Item_Option WHERE MaOrderItem IN (SELECT MaOrderItem FROM Order_Item WHERE MaOrder = $orderId)");
    $pdo->exec("DELETE FROM Order_Item WHERE MaOrder = $orderId");
    $pdo->exec("DELETE FROM Orders WHERE MaOrder = $orderId");

    $pdo->commit();

    // Set cart session
    if (!empty($cart)) {
        $_SESSION['cart'] = $cart;
        // Optionally save to DB if needed
        $storeId = isset($_SESSION['selected_store']) ? (int) $_SESSION['selected_store'] : 1;
        saveCartToDB($user['id'], $storeId);
    }

    $response['success'] = true;
    $response['message'] = 'Đã hủy giao dịch';

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
