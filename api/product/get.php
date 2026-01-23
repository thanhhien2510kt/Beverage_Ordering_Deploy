<?php
header('Content-Type: application/json');
require_once '../../functions.php';

$response = ['success' => false, 'data' => null, 'message' => ''];
try {
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$productId) {
        throw new Exception('Mã sản phẩm không hợp lệ');
    }
    
    $product = getProductById($productId);
    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại');
    }
    
    $optionsData = getProductOptions($productId);
    
    $optionGroups = [];
    foreach ($optionsData as $option) {
        $groupId = $option['MaOptionGroup'];
        if (!isset($optionGroups[$groupId])) {
            $optionGroups[$groupId] = [
                'MaOptionGroup' => $option['MaOptionGroup'],
                'TenNhom' => $option['TenNhom'],
                'IsMultiple' => (bool)$option['IsMultiple'],
                'options' => []
            ];
        }
        $optionGroups[$groupId]['options'][] = [
            'MaOptionValue' => $option['MaOptionValue'],
            'TenGiaTri' => $option['TenGiaTri'],
            'GiaThem' => (float)$option['GiaThem']
        ];
    }
    
    $response = [
        'success' => true,
        'data' => [
            'product' => [
                'MaSP' => $product['MaSP'],
                'TenSP' => $product['TenSP'],
                'GiaNiemYet' => (float)($product['GiaNiemYet'] ?? $product['GiaCoBan']),
                'GiaCoBan' => (float)($product['GiaCoBan'] ?? $product['GiaNiemYet']),
                'HinhAnh' => $product['HinhAnh'] ?? 'assets/img/products/product_one.png'
            ],
            'optionGroups' => array_values($optionGroups)
        ]
    ];
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
