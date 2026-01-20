<?php
/**
 * Menu Page - Danh sách sản phẩm
 * Hiển thị products từ database với filter theo category và search
 */

require_once '../../functions.php';

// Start session to check user role
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user can add to cart (only customers can add to cart)
$canAddToCart = true;
if (isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);
    // Hide add to cart button for admin and staff
    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        $canAddToCart = false;
    }
}

// Get parameters
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
                <aside >
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
                            <li class="category-item <?php echo $categoryId == $category['MaCategory'] ? 'active' : ''; ?>">
                                <a href="?category=<?php echo $category['MaCategory']; ?>&search=<?php echo urlencode($keyword); ?>" class="category-link">
                                    <span class="category-icon">
                                        <?php
                                        $icon = getCategoryIcon($category['TenCategory']);
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
                                             alt="<?php echo e($category['TenCategory']); ?>" 
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

    <!-- Product Customization Side Menu -->
    <div id="product-customize-modal" class="product-customize-modal">
        <div class="modal-overlay"></div>
        <div class="modal-side-panel">
            <!-- Close Button -->
            <button type="button" id="close-modal-btn" class="modal-close-btn" aria-label="Đóng">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            
            <div class="modal-content">
                <div id="modal-loading" class="modal-loading">
                    <p>Đang tải...</p>
                </div>
                
                <div id="modal-product-content" style="display: none;">
                    <!-- Product Image -->
                    <div class="modal-product-image-wrapper">
                        <img id="modal-product-image" src="" alt="" class="modal-product-image">
                    </div>
                    
                    <!-- Product Info -->
                    <div class="modal-product-info">
                        <h2 id="modal-product-name"></h2>
                        <div class="modal-product-price">
                            <span id="modal-current-price" class="modal-current-price"></span>
                            <span id="modal-old-price" class="modal-old-price"></span>
                        </div>
                        
                        <!-- Quantity Selector -->
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" id="modal-decrease-qty">-</button>
                            <!-- Giữ input ẩn để logic JS dùng, hiển thị span để người dùng thấy -->
                            <input type="hidden" id="modal-quantity" value="1" min="1" max="10">
                            <span id="modal-quantity-display" class="quantity-input" aria-live="polite">1</span>
                            <button type="button" class="quantity-btn" id="modal-increase-qty">+</button>
                        </div>
                        
                        <!-- Options Form -->
                        <form id="modal-product-form">
                            <input type="hidden" id="modal-product-id" name="product_id">
                            <input type="hidden" id="modal-base-price" name="base_price">
                            <input type="hidden" id="modal-reference-price" name="reference_price">
                            
                            <div id="modal-option-groups" style="margin-top: 20px;"></div>
                            
                            <!-- Note Section -->
                            <div class="note-section">
                                <label for="modal-product-note" class="note-label">Thêm ghi chú</label>
                                <textarea 
                                    id="modal-product-note" 
                                    name="note" 
                                    class="note-textarea" 
                                    placeholder="Nhập nội dung ghi chú cho quán (nếu có)"
                                    maxlength="52"
                                ></textarea>
                                <div class="char-count"><span id="modal-char-count">0</span>/52 ký tự</div>
                            </div>
                            
                            <!-- Total Price Display -->
                            <div class="total-price-display">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 20px; font-weight: bold; color: var(--primary-green);">Tổng tiền:</span>
                                    <span id="modal-total-price" style="font-size: 28px; font-weight: bold; color: var(--primary-green);"></span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <?php if ($canAddToCart): ?>
                            <div class="product-actions" style="position: fixed; bottom: 0; background-color: var(--white); padding: 20px; border-top: 1px solid var(--border-color);">
                                <button type="button" id="modal-add-to-cart-btn" class="btn-add-cart">
                                    Thêm vào giỏ
                                </button>
                                
                                    <a href="../cart/index.php">
                                        <button type="button" id="modal-add-to-cart-btn" class="btn-view-cart" style="width: 250px;">
                                        Xem giỏ hàng
                                    </a>
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="product-actions" style="position: fixed; bottom: 0; background-color: var(--white); padding: 20px; border-top: 1px solid var(--border-color); text-align: center;">
                                <p style="color: var(--text-light); font-size: 14px;">Tài khoản Admin/Staff không thể thêm sản phẩm vào giỏ hàng</p>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <?php include '../../components/snack-bar.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    <script src="../../assets/js/menu.js"></script>
</body>
</html>
