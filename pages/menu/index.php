<?php

require_once '../../functions.php';


if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}


$canAddToCart = true;
if (isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);

    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        $canAddToCart = false;
    }
}


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
        if ($cat['macategory'] == $categoryId) {
            $selectedCategoryName = $cat['tencategory'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/menu.css">
    <link rel="stylesheet" href="../../assets/css/menu-modal.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- Hero Banner Section -->
    <section class="menu-hero" id="menu-hero-section">
        <div class="menu-hero-image">
            <img src="../../assets/img/products/product_banner.png" alt="Fresh Juice">
        </div>
    </section>

    <!-- Menu Content -->
    <section class="menu-content-section" id="menu-content-section">
        <div class="container">
            <!-- Menu Title and Search Bar -->
            <div class="menu-header">
                <h1 class="sidebar-title">Menu</h1>
                <div class="menu-search">
                        <form method="GET" action="" class="search-form">
                        <?php if ($categoryId): ?>
                            <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <?php if ($showBestSeller): ?>
                            <input type="hidden" name="bestseller" value="1">
                        <?php endif; ?>
                        <?php if ($showTopping): ?>
                            <input type="hidden" name="topping" value="1">
                        <?php endif; ?>
                        <div class="search-input-wrapper">
                            <input 
                                type="text" 
                                name="search" 
                                class="search-input" 
                                placeholder="Hôm nay bạn muốn uống gì?" 
                                value="<?php echo e($keyword); ?>"
                            >
                            <svg class="search-mic-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                <line x1="12" y1="19" x2="12" y2="23"/>
                                <line x1="8" y1="23" x2="16" y2="23"/>
                            </svg>
                        </div>
                        
                    </form>
                </div>
            </div>

            <div class="menu-layout">
                <!-- Sidebar - Categories -->
                <aside class="menu-sidebar">
                    <ul class="category-list">
                        <li class="category-item <?php echo $showBestSeller ? 'active' : ''; ?>">
                            <a href="?bestseller=1&search=<?php echo urlencode($keyword); ?>" class="category-link">
                                <span class="category-icon">
                                    <img src="../../assets/img/products/menu/best_seller.svg" 
                                         alt="Best Seller" 
                                         class="category-icon-img">
                                </span>
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li class="category-item <?php echo $categoryId == $category['macategory'] ? 'active' : ''; ?>">
                                <a href="?category=<?php echo $category['macategory']; ?>&search=<?php echo urlencode($keyword); ?>" class="category-link">
                                    <span class="category-icon">
                                        <?php
                                        $icon = getCategoryIcon($category['tencategory']);
                                        $iconMap = [
                                            'coffee' => 'coffee.svg',
                                            'milk-tea' => 'milk_tea.svg',
                                            'fruit-tea' => 'fruit_tea.svg',
                                            'blended' => 'grinded_ice.svg',
                                            'yogurt' => 'yogurt.svg',
                                            'topping' => 'topping.svg',
                                            'default' => 'coffee.svg'
                                        ];
                                        $iconFile = $iconMap[$icon] ?? 'coffee.svg';
                                        ?>
                                        <img src="../../assets/img/products/menu/<?php echo $iconFile; ?>" 
                                             alt="<?php echo e($category['tencategory']); ?>" 
                                             class="category-icon-img">
                                    </span>
                                    
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="category-item <?php echo $showTopping ? 'active' : ''; ?>">
                            <a href="?topping=1&search=<?php echo urlencode($keyword); ?>" class="category-link">
                                <span class="category-icon">
                                    <img src="../../assets/img/products/menu/topping.svg" 
                                         alt="Topping" 
                                         class="category-icon-img">
                                </span>
                            </a>
                        </li>
                    </ul>
                </aside>

                <!-- Main Content - Products -->
                <main class="menu-main">

                    <!-- Products Grid -->
                    <div class="menu-products-section">
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

                        <div id="menu-products-wrapper"
                             data-category-id="<?php echo $categoryId ?: ''; ?>"
                             data-bestseller="<?php echo $showBestSeller ? '1' : '0'; ?>"
                             data-topping="<?php echo $showTopping ? '1' : '0'; ?>"
                             data-page="<?php echo $page; ?>"
                             data-per-page="<?php echo $perPage; ?>">
                            <?php if ($showBestSeller && !empty($bestSellers)): ?>
                                <!-- Render Best Sellers -->
                                <div class="products-grid">
                                    <?php foreach ($bestSellers as $product): ?>
                                        <?php 
                                            $product = $product;
                                            $basePath = '../../'; // Từ pages/menu/ về root
                                            include '../../components/product-card.php'; 
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($showTopping && !empty($toppings)): ?>
                                <!-- Render Toppings -->
                                <div class="products-grid">
                                    <?php foreach ($toppings as $topping): ?>
                                        <?php 
                                            $product = $topping;
                                            $basePath = '../../'; // Từ pages/menu/ về root
                                            include '../../components/product-card.php'; 
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (!$showBestSeller && !$showTopping && !empty($products)): ?>
                                <!-- Render Regular Products -->
                                <div class="products-grid">
                                    <?php foreach ($products as $product): ?>
                                        <?php 
                                            $product = $product;
                                            $basePath = '../../'; // Từ pages/menu/ về root
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

                            <!-- Pagination -->
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
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>

    <?php 
        $bgType = 'light-green';
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/product-customize-modal.php'; ?>

    <?php include '../../components/footer.php'; ?>
    <?php include '../../components/snack-bar.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    <script src="../../assets/js/menu.js"></script>
</body>
</html>
