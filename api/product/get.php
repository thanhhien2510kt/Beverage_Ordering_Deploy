<?php
ob_start(); // Bắt mọi warning/notice PHP, không cho in ra trước JSON
header('Content-Type: application/json');
require_once '../../functions.php';

$response = ['success' => false, 'data' => null, 'message' => ''];
try {
    $productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if (!$productId) {
        throw new Exception('Mã sản phẩm không hợp lệ');
    }

    $pdo = getDBConnection();

    // Lấy thông tin sản phẩm
    $stmt = $pdo->prepare("
        SELECT sp.MaSP, sp.TenSP, sp.GiaCoBan, sp.GiaNiemYet, sp.HinhAnh
        FROM SanPham sp
        WHERE sp.MaSP = ? AND sp.TrangThai = 1
    ");
    $stmt->execute([$productId]);
    $productRaw = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($productRaw) {
        $product = array_change_key_case($productRaw, CASE_LOWER);
    } else {
        $product = false;
    }

    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại');
    }

    // Lấy các nhóm tùy chọn (Size, Đường, Đá, Topping)
    $stmtOpt = $pdo->prepare("
        SELECT og.MaOptionGroup, og.TenNhom, og.IsMultiple,
               ov.MaOptionValue, ov.TenGiaTri, ov.GiaThem
        FROM Product_Option_Group pog
        INNER JOIN Option_Group og ON pog.MaOptionGroup = og.MaOptionGroup
        INNER JOIN Option_Value ov  ON ov.MaOptionGroup = og.MaOptionGroup
        WHERE pog.MaSP = ?
        ORDER BY og.MaOptionGroup, 
            CASE 
                WHEN ov.TenGiaTri LIKE '%Size S%' THEN 1
                WHEN ov.TenGiaTri LIKE '%Size M%' THEN 2
                WHEN ov.TenGiaTri LIKE '%Size L%' THEN 3
                ELSE ov.MaOptionValue 
            END
    ");
    $stmtOpt->execute([$productId]);
    $optionsData = $stmtOpt->fetchAll(PDO::FETCH_ASSOC);

    $optionGroups = [];
    foreach ($optionsData as $optRaw) {
        $option = array_change_key_case($optRaw, CASE_LOWER);
        $groupId = $option['maoptiongroup'];
        if (!isset($optionGroups[$groupId])) {
            $optionGroups[$groupId] = [
                'MaOptionGroup' => $option['maoptiongroup'],
                'TenNhom' => $option['tennhom'],
                'IsMultiple' => (bool) $option['ismultiple'],
                'options' => []
            ];
        }
        $optionGroups[$groupId]['options'][] = [
            'MaOptionValue' => $option['maoptionvalue'],
            'TenGiaTri' => $option['tengiatri'],
            'GiaThem' => (float) $option['giathem']
        ];
    }

    $response = [
        'success' => true,
        'data' => [
            'product' => [
                'MaSP' => $product['masp'],
                'TenSP' => $product['tensp'],
                'GiaCoBan' => (float) ($product['giacoban'] ?? 0),
                'GiaNiemYet' => (float) ($product['gianiemyet'] ?? $product['giacoban'] ?? 0),
                'HinhAnh' => $product['hinhanh'] ?? 'assets/img/products/product_one.png'
            ],
            'optionGroups' => array_values($optionGroups)
        ]
    ];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

ob_end_clean(); // Xóa mọi warning PHP đã buffer
echo json_encode($response, JSON_UNESCAPED_UNICODE);
