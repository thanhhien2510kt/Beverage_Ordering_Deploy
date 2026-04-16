<?php
if (!isset($product)) return;

$productName = e($product['tensp']);
$isTopping = isset($product['istopping']) && $product['istopping'] === true;
$giaNiemYet = isset($product['gianiemyet']) ? (float)$product['gianiemyet'] : (float)($product['giacoban'] ?? 0);
$giaCoBan = isset($product['giacoban']) ? (float)$product['giacoban'] : $giaNiemYet;
$productPrice = formatCurrency($giaNiemYet);
$productId = $product['masp'];
$showOldPrice = (!$isTopping && $giaCoBan > $giaNiemYet);


$rating = isset($product['rating']) && $product['rating'] !== null ? (float)$product['rating'] : 0;
$ratingCount = isset($product['soluotrating']) ? (int)$product['soluotrating'] : 0;


$ratingValue = $rating > 0 ? number_format($rating, 1, ',', '.') : '0,0';


$starsDisplay = renderStars($rating);


$imagePath = !empty($product['hinhanh']) ? $product['hinhanh'] : 'assets/img/products/product_one.png';


if (!isset($basePath)) {

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $callerFile = isset($backtrace[1]['file']) ? $backtrace[1]['file'] : __FILE__;
    $callerDir = dirname($callerFile);
    $rootDir = dirname(__DIR__); // Root của project (parent của components/)
    

    $callerDir = realpath($callerDir);
    $rootDir = realpath($rootDir);
    
    if ($callerDir && $rootDir && strpos($callerDir, $rootDir) === 0) {

        $relativePath = str_replace($rootDir, '', $callerDir);
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
        $levels = $relativePath ? substr_count($relativePath, DIRECTORY_SEPARATOR) + 1 : 0;
        

        $basePath = $levels > 0 ? str_repeat('../', $levels) : '';
    } else {

        $basePath = '';
    }
}

$basePath = rtrim($basePath, '/\\');
if ($basePath) {
    $basePath .= '/';
}

$imagePath = ltrim($imagePath, '/\\');


$productImage = $basePath . $imagePath;
$fallbackImage = $basePath . 'assets/img/products/product_one.png';


$canAddToCart = true;
if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}
if (isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);

    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        $canAddToCart = false;
    }
}
?>
<div class="product-card" data-product-id="<?php echo $productId; ?>">
    <div class="product-image-wrapper">
        <img src="<?php echo e($productImage); ?>" 
             alt="<?php echo $productName; ?>" 
             class="product-image" 
             onerror="this.onerror=null; if(this.src !== '<?php echo e($fallbackImage); ?>') { this.src='<?php echo e($fallbackImage); ?>'; } else { this.style.display='none'; }">
    </div>
    <div class="product-info">
        <h3 class="product-name"><?php echo $productName; ?></h3>
        <div class="product-rating">
            <span class="stars"><?php echo $starsDisplay; ?></span>
            <span class="rating-value"><?php echo $ratingValue; ?></span>
            <?php if ($ratingCount > 0): ?>
                <span class="rating-count">(<?php echo number_format($ratingCount, 0, ',', '.'); ?> đánh giá)</span>
            <?php else: ?>
                <span class="rating-count">(Chưa có đánh giá)</span>
            <?php endif; ?>
        </div>
        <div class="product-price">
            <div class="price-info">
                <span class="current-price"><?php echo $productPrice; ?></span>
                <?php if (isset($showOldPrice) && $showOldPrice): ?>
                    <span class="old-price"><?php echo formatCurrency($giaCoBan); ?></span>
                <?php endif; ?>
            </div>
            <?php if (!$isTopping && $canAddToCart): ?>
                <button class="add-to-cart-btn" data-product-id="<?php echo $productId; ?>" title="Thêm vào giỏ hàng">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
