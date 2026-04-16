<?php

require_once 'functions.php';


$carouselDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'carousel';
$carouselImages = [];
if (is_dir($carouselDir)) {
    $files = scandir($carouselDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($carouselDir . DIRECTORY_SEPARATOR . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $carouselImages[] = 'assets/img/carousel/' . $file;
            }
        }
    }

    sort($carouselImages);
}


$categories = getCategories();
$bestSellerProducts = getProductsByCategory(null, 4);
$news = getNews(3); 
$stores = getStores(1); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeowTea Fresh - Trang Chủ</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'components/header.php'; ?>

    <!-- Hero Section - Carousel -->
    <section class="hero-section">
        <?php 
            $images = !empty($carouselImages) ? $carouselImages : ['assets/img/carousel/one.png'];
            $carouselId = 'hero-carousel';
            $autoPlayInterval = 500;
            include 'components/carousel.php';
        ?>
    </section>

    <!-- About Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);">MeowTea Fresh</h2>
            <p class="section-subtitle" style="text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.15);">Hương vị tươi mới, trải nghiệm đẳng cấp</p>
            <p style="text-align: justify; max-width: 800px; margin: 0 auto; color: var(--text-light); line-height: 1.8; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);">
                MeowTea Fresh tự hào là thương hiệu đồ uống hàng đầu với cam kết mang đến những ly trà sữa, cà phê và trà trái cây tươi ngon nhất. 
                Chúng tôi chọn lọc nguyên liệu chất lượng cao, pha chế theo công thức độc quyền và phục vụ với tâm huyết trong từng sản phẩm. 
                Đến với MeowTea Fresh, bạn không chỉ thưởng thức một ly đồ uống, mà còn trải nghiệm một không gian thư giãn tuyệt vời và dịch vụ chuyên nghiệp.
            </p>
        </div>
    </section>

    <!-- Product Categories Section -->
    <section class="categories-section section">
        <div class="container">
            <div class="categories-grid">
                <div class="category-card" onclick="window.location.href='pages/menu/index.php?category=1'">
                    <h3 class="category-name">Cà Phê</h3>
                    <img src="assets/img/product_catalogue/coffee.png" alt="Cà Phê" class="category-image">
                    
                </div>
                <div class="category-card" onclick="window.location.href='pages/menu/index.php?category=2'">
                    <h3 class="category-name">Trà Sữa</h3>
                    <img src="assets/img/product_catalogue/milk-tea.jpg" alt="Trà Sữa" class="category-image">
                    
                </div>
                <div class="category-card" onclick="window.location.href='pages/menu/index.php?category=3'">
                    <h3 class="category-name">Trà Trái Cây</h3>
                    <img src="assets/img/product_catalogue/fruit-tea.png" alt="Trà Trái Cây" class="category-image">
                    
                </div>
            </div>
            <div class="btn-center">
                <?php 
                    $text = 'ĐẶT NGAY';
                    $type = 'primary';
                    $href = 'pages/menu/index.php';
                    $width = '200px';
                    include 'components/button.php';
                ?>
            </div>
        </div>
    </section>

    <!-- Store System Section -->
    <section class="store-system-section section">
        <div class="container">
            <div class="store-system-content">
                <div>
                    <img src="assets/img/stores/stores_banner.png" alt="Cửa hàng MeowTea Fresh" class="store-image" style="height: 320px; max-width: 100%;">
                </div>
                <div class="store-info">
                    <h3 style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); font-size: 28px;">Hệ Thống Cửa Hàng</h3>
                    <p class="store-count" style="text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.15); font-size: 16px; font-weight: bold;">12 cửa hàng trên toàn quốc</p>
                    <p class="store-description" style="text-align: justify; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1); font-size: 15px;">
                        Với hệ thống 12 cửa hàng trải dài trên khắp cả nước, MeowTea Fresh mang đến trải nghiệm đồ uống tươi ngon và không gian thư giãn hiện đại cho mọi khách hàng. 
                        Mỗi cửa hàng được thiết kế với phong cách riêng biệt nhưng vẫn giữ được bản sắc thương hiệu, tạo nên điểm đến lý tưởng cho những ai yêu thích trà sữa và cà phê chất lượng.
                    </p>
                    <div class="btn-start">
                        <?php 
                            $text = 'Xem thêm';
                            $type = 'outline';
                            $href = 'pages/stores/index.php';
                            $width = '200px';
                            include 'components/button.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Seller Section -->
    <section class="best-seller-section">
        <div class="container">
            <div class="best-seller-header">
                <h2 class="best-seller-title" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);">Best Seller</h2>
                <a href="pages/menu/index.php" class="view-all-link">Xem tất cả >></a>
            </div>
            <div class="products-grid">
                <?php if (!empty($bestSellerProducts)): ?>
                    <?php foreach ($bestSellerProducts as $product): ?>
                        <?php 
                            $product = $product;
                            $basePath = ''; 
                            include 'components/product-card.php'; 
                        ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: var(--text-light);">
                        Chưa có sản phẩm nào. Vui lòng thêm sản phẩm vào database.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- News & Events Section -->
    <section class="news-section">
        <div class="container">
            <div class="news-header" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);">
                <h2 class="news-title">Tin Tức & Sự Kiện</h2>
                <a href="pages/news/index.php" class="view-all-link">Xem tất cả >></a>
            </div>
            <div class="news-grid">
                <?php if (!empty($news)): ?>
                    <?php foreach ($news as $newsItem): ?>
                        <?php 
                            $news = $newsItem;
                            include 'components/news-card.php'; 
                        ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback news items if database is empty -->
                    <div class="news-card">
                        <div class="news-image-wrapper">
                            <img src="assets/img/news/news_one.jpg" alt="News" class="news-image">
                            <div class="news-date-badge">
                                <span class="date-day">24</span>
                                <span class="date-month">THG 12</span>
                            </div>
                        </div>
                        <div class="news-content">
                            <h3 class="news-title">
                                <a href="pages/news/index.php">Những lợi ích tuyệt vời của nước ép trái cây đối với sức khỏe</a>
                            </h3>
                            <p class="news-excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore...</p>
                            <a href="pages/news/index.php" class="news-read-more">Đọc tiếp →</a>
                        </div>
                    </div>
                    <div class="news-card">
                        <div class="news-image-wrapper">
                            <img src="assets/img/news/news_two.jpg" alt="News" class="news-image">
                            <div class="news-date-badge">
                                <span class="date-day">05</span>
                                <span class="date-month">THG 12</span>
                            </div>
                        </div>
                        <div class="news-content">
                            <h3 class="news-title">
                                <a href="pages/news/index.php">Cà Phê Cappuccino Dừa lần đầu tiên có mặt tại MeowTea Fresh</a>
                            </h3>
                            <p class="news-excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore...</p>
                            <a href="pages/news/index.php" class="news-read-more">Đọc tiếp →</a>
                        </div>
                    </div>
                    <div class="news-card">
                        <div class="news-image-wrapper">
                            <img src="assets/img/news/news_three.png" alt="News" class="news-image">
                            <div class="news-date-badge">
                                <span class="date-day">09</span>
                                <span class="date-month">THG 12</span>
                            </div>
                        </div>
                        <div class="news-content">
                            <h3 class="news-title">
                                <a href="pages/news/index.php">MeowTea Fresh ra mắt dòng sản phẩm Matcha - dấu ấn độc đáo</a>
                            </h3>
                            <p class="news-excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore...</p>
                            <a href="pages/news/index.php" class="news-read-more">Đọc tiếp →</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </section>
    <?php include 'components/back-to-top.php'; ?>

    <?php 
        $basePath = '';
        include 'components/product-customize-modal.php'; 
    ?>

    <?php include 'components/footer.php'; ?>
    <?php include 'components/snack-bar.php'; ?>

    <script src="assets/js/common.js"></script>
    <script src="assets/js/snack-bar.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
