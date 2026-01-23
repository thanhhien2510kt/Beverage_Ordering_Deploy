<?php
header('Content-Type: application/json');
require_once '../../functions.php';

$response = ['success' => false, 'stores' => [], 'total' => 0, 'message' => ''];
try {
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $province = isset($_GET['province']) ? trim($_GET['province']) : '';
    $ward = isset($_GET['ward']) ? trim($_GET['ward']) : '';
    

    $stores = getStoresWithFilters($keyword, $province, $ward);
    $total = countStores($keyword, $province, $ward);
    

    $formattedStores = [];
    foreach ($stores as $store) {
        $formattedStores[] = [
            'MaStore' => (int)$store['MaStore'],
            'TenStore' => $store['TenStore'],
            'DiaChi' => $store['DiaChi'],
            'DienThoai' => $store['DienThoai'],
            'HinhAnh' => $store['HinhAnh'] ?? 'assets/img/stores/store_default.jpg',
            'GioMoCua' => $store['GioMoCua'] ?? '22:00'
        ];
    }
    
    $response = [
        'success' => true,
        'stores' => $formattedStores,
        'total' => $total
    ];
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
