<?php

require_once '../../functions.php';


$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 6; // Hiển thị 6 bài viết mỗi trang


$pdo = getDBConnection();
$countStmt = $pdo->query("SELECT COUNT(*) as total FROM News WHERE TrangThai = 1");
$totalNews = $countStmt->fetch()['total'] ?? 0;
$totalPages = ceil($totalNews / $perPage);


$offset = ($page - 1) * $perPage;
$sql = "SELECT * FROM News WHERE TrangThai = 1 ORDER BY NgayTao DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- News Banner Section -->
    <section class="news-banner-section" id="news-banner-section" style="background-image: url('../../assets/img/news/news_banner.jpg'); background-size: cover; background-position: center; margin-bottom: 40px;">
    </section>

    <!-- News Content Section -->
    <section class="news-content-section">
        <div class="container">
            <h1 class="news-title" style="font-size: 36px; font-weight: bold; color: var(--primary-green); text-align: center; margin-bottom: 40px;">Tin Tức & Sự Kiện</h1>
            
            <?php if (!empty($news)): ?>
                <div class="news-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-bottom: 40px;">
                    <?php foreach ($news as $newsItem): ?>
                        <?php 
                            $news = $newsItem;
                            include '../../components/news-card.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 0): ?>
                    <?php
                        $queryParams = [];
                        include '../../components/pagination.php';
                    ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-news" style="text-align: center; padding: 60px 20px;">
                    <p style="font-size: 18px; color: var(--text-light); margin-bottom: 20px;">Chưa có tin tức nào.</p>
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
    <script src="../../assets/js/news.js"></script>
</body>
</html>
