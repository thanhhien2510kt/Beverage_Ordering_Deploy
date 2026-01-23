<?php
header('Content-Type: application/json');
require_once '../../functions.php';

$response = [
    'success' => false,
    'headingHtml' => '',
    'contentHtml' => '',
];
try {
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
    $showBestSeller = isset($_GET['bestseller']) && $_GET['bestseller'] == '1';
    $showTopping = isset($_GET['topping']) && $_GET['topping'] == '1';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 12;

    $categories = getCategories();
    $bestSellers = getBestSellerProducts(2);
    $toppings = $showTopping ? getToppings() : [];
    $products = !$showTopping ? searchProducts($keyword, $categoryId, $page, $perPage) : [];
    $totalProducts = !$showTopping ? countProducts($keyword, $categoryId) : count($toppings);
    $totalPages = !$showTopping ? ceil($totalProducts / $perPage) : 1;

    $selectedCategoryName = 'Tất cả';
    if ($showBestSeller) {
        $selectedCategoryName = 'Best Seller';
    } elseif ($showTopping) {
        $selectedCategoryName = 'Topping';
    } elseif ($categoryId) {
        foreach ($categories as $cat) {
            if ($cat['MaCategory'] == $categoryId) {
                $selectedCategoryName = $cat['TenCategory'];
                break;
            }
        }
    }

    ob_start();
    ?>
    <h2 class="section-heading" id="menu-section-heading">
        <?php echo e($selectedCategoryName); ?>
        <?php if ($showBestSeller && !empty($bestSellers)): ?>
            <span class="product-count">(<?php echo count($bestSellers); ?> sản phẩm)</span>
        <?php elseif ($showTopping && !empty($toppings)): ?>
            <span class="product-count">(<?php echo count($toppings); ?> topping)</span>
        <?php elseif (!$showBestSeller && !$showTopping && $totalProducts > 0): ?>
            <span class="product-count">(<?php echo $totalProducts; ?> sản phẩm)</span>
        <?php endif; ?>
    </h2>
    <?php
    $headingHtml = ob_get_clean();

    ob_start();
    ?>
    <?php if ($showBestSeller && !empty($bestSellers)): ?>
        <div class="products-grid">
            <?php foreach ($bestSellers as $product): ?>
                <?php 
                    $product = $product;
                    $basePath = '../../';
                    include '../../components/product-card.php'; 
                ?>
            <?php endforeach; ?>
        </div>
    <?php elseif ($showTopping && !empty($toppings)): ?>
        <div class="products-grid">
            <?php foreach ($toppings as $topping): ?>
                <?php 
                    $product = $topping;
                    $basePath = '../../';
                    include '../../components/product-card.php'; 
                ?>
            <?php endforeach; ?>
        </div>
    <?php elseif (!$showBestSeller && !$showTopping && !empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <?php 
                    $product = $product;
                    $basePath = '../../';
                    include '../../components/product-card.php'; 
                ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-products">
            <p>Không tìm thấy sản phẩm nào.</p>
            <?php 
                $text = 'Xem tất cả sản phẩm';
                $type = 'primary';
                $href = 'index.php';
                $width = 'auto';
                include '../../components/button.php';
            ?>
        </div>
    <?php endif; ?>

    <?php if (!$showBestSeller && !$showTopping && $totalPages > 0): ?>
        <?php
            $queryParams = [];
            if ($categoryId) {
                $queryParams['category'] = $categoryId;
            }
            if ($keyword) {
                $queryParams['search'] = $keyword;
            }
            $ajaxMode = true;
            include '../../components/pagination.php';
        ?>
    <?php endif; ?>
    <?php
    $contentHtml = ob_get_clean();

    $response['success'] = true;
    $response['headingHtml'] = $headingHtml;
    $response['contentHtml'] = $contentHtml;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
