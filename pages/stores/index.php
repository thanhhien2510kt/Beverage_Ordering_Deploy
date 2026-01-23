<?php

require_once '../../functions.php';


$carouselDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'stores';
$carouselImages = [];
if (is_dir($carouselDir)) {
    $files = scandir($carouselDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($carouselDir . DIRECTORY_SEPARATOR . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && preg_match('/^\d+\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $carouselImages[] = '../../assets/img/stores/' . $file;
            }
        }
    }

    usort($carouselImages, function($a, $b) {
        $numA = (int)preg_replace('/[^0-9]/', '', basename($a));
        $numB = (int)preg_replace('/[^0-9]/', '', basename($b));
        return $numA - $numB;
    });
}


$searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$searchProvince = isset($_GET['province']) ? trim($_GET['province']) : '';
$searchWard = isset($_GET['ward']) ? trim($_GET['ward']) : '';


$stores = getStoresWithFilters($searchKeyword, $searchProvince, $searchWard);
$totalStores = countStores($searchKeyword, $searchProvince, $searchWard);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/stores.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- Hero Section - Carousel -->
    <section class="stores-hero">
        <?php 
            $images = !empty($carouselImages) ? $carouselImages : ['../../assets/img/stores/stores_banner.png'];
            $carouselId = 'stores-carousel';
            $autoPlayInterval = 3000;
            include '../../components/carousel.php';
        ?>
    </section>

    <!-- Stores Content -->
    <section class="stores-content section">
        <div class="container">
            <h1 class="stores-title">Hệ Thống Cửa hàng MeowTea Fresh</h1>

            <!-- Search Section -->
            <div class="stores-search-section">
                <!-- Search by Name -->
                <div class="search-group">
                    <label class="search-group-label">Tìm kiếm theo tên cửa hàng</label>
                    <div class="search-input-wrapper">
                        <input 
                            type="text" 
                            id="search-keyword" 
                            class="search-input" 
                            placeholder="Nhập từ khóa"
                            value="<?php echo e($searchKeyword); ?>"
                        >
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                </div>

                <!-- Search by Location -->
                <div class="search-group">
                    <label class="search-group-label">Tìm kiếm theo vị trí</label>
                    <div class="location-selects">
                        <select id="search-province" class="location-select dropdown-select">
                            <option value="">Tỉnh/Thành phố</option>
                            <option value="Hồ Chí Minh" <?php echo $searchProvince === 'Hồ Chí Minh' ? 'selected' : ''; ?>>Hồ Chí Minh</option>
                            <option value="Hà Nội" <?php echo $searchProvince === 'Hà Nội' ? 'selected' : ''; ?>>Hà Nội</option>
                            <option value="Đà Nẵng" <?php echo $searchProvince === 'Đà Nẵng' ? 'selected' : ''; ?>>Đà Nẵng</option>
                            <option value="Cần Thơ" <?php echo $searchProvince === 'Cần Thơ' ? 'selected' : ''; ?>>Cần Thơ</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stores Count -->
            <div class="stores-count">
                Tất cả có <?php echo $totalStores; ?> cửa hàng
            </div>

            <!-- Stores Grid -->
            <?php if (count($stores) > 0): ?>
                <div class="stores-grid" id="stores-grid">
                    <?php foreach ($stores as $store): ?>
                        <div class="store-card">
                            <img 
                                src="../../assets/img/stores/<?php echo $store['MaStore']; ?>.jpg" 
                                alt="<?php echo e($store['TenStore']); ?>"
                                class="store-image"
                                onerror="this.src='../../assets/img/products/product_banner.png'"
                            >
                            <div class="store-info">
                                <h3 class="store-name">MeowTea Fresh<br><?php echo e($store['TenStore']); ?></h3>
                                <div class="store-hours">Mở cửa đến 22:00</div>
                                <div class="store-address">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span><?php echo e($store['DiaChi']); ?></span>
                                </div>
                                <div class="store-phone">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span><?php echo e($store['DienThoai']); ?></span>
                                </div>
                                <div class="store-actions">
                                    <?php 
                                        $text = 'Đặt ngay';
                                        $type = 'primary';
                                        $href = '../menu/index.php';
                                        $class = 'btn-order';
                                        $width = '150px';
                                        include '../../components/button.php';
                                    ?>
                                    <?php 
                                        $text = 'Chỉ đường';
                                        $type = 'outline';
                                        $href = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($store['DiaChi']);
                                        $class = 'btn-directions';
                                        $width = '200px';
                                        $target = '_blank';
                                        include '../../components/button.php';
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-stores">
                    Không tìm thấy cửa hàng nào phù hợp với tiêu chí tìm kiếm của bạn.
                </div>
            <?php endif; ?>
        </div>

       
    </section>

    <?php 
        $bgType = 'light-green';
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/snack-bar.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    <script src="../../assets/js/stores.js"></script>
</body>
</html>
