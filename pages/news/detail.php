<?php
require_once '../../functions.php';

$newsId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$newsId) {
    header('Location: index.php');
    exit;
}

$news = getNewsById($newsId);

if (!$news) {
    header('Location: index.php');
    exit;
}

// Lấy nội dung Markdown
$content = '';
if (!empty($news['noidung'])) {
    $content = readMarkdownFile($news['noidung']);
}

$newsTitle = e($news['tieude']);
$newsImage = !empty($news['hinhanh']) ? '../../' . ltrim($news['hinhanh'], '/') : '../../assets/img/news/news_one.jpg';
$newsDate = date('d/m/Y', strtotime($news['ngaytao']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $newsTitle; ?> - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/news-card.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- marked.js for markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="news-detail-page">
    <?php include '../../components/header.php'; ?>

    <section class="news-detail-section" style="padding: 140px 0 80px; background-color: #E8EDE8; min-height: 80vh;">
        <div class="container" style="max-width: 900px; background-color: #fff; padding: 40px; border-radius: 30px; box-shadow: var(--shadow);">
            <!-- Breadcrumb -->
            <nav style="margin-bottom: 30px;">
                <a href="index.php" style="color: var(--text-light); text-decoration: none;">Tin tức</a>
                <span style="margin: 0 10px; color: var(--text-light);">/</span>
                <span style="color: var(--primary-green); font-weight: bold;">Chi tiết</span>
            </nav>

            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <span style="padding: 8px 15px; background-color: var(--primary-green); color: #fff; border-radius: 5px; font-weight: bold; font-size: 14px;">
                    <?php echo $newsDate; ?>
                </span>
                <span style="color: var(--text-light); font-size: 14px;">Bởi MeowTea Fresh</span>
            </div>

            <h1 style="font-size: 36px; font-weight: bold; color: var(--primary-green); margin-bottom: 30px; line-height: 1.3;">
                <?php echo $newsTitle; ?>
            </h1>

            <div style="margin-bottom: 40px; border-radius: 20px; overflow: hidden; box-shadow: var(--shadow);">
                <img src="<?php echo $newsImage; ?>" alt="<?php echo $newsTitle; ?>" style="width: 100%; height: auto; display: block;">
            </div>

            <div id="markdown-content" class="markdown-body" style="line-height: 1.8; color: var(--text-dark); font-size: 18px;">
                <!-- Content will be rendered by JS below to handle markdown -->
                <textarea id="raw-markdown" style="display: none;"><?php echo $content; ?></textarea>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 60px 0 40px;">

            <div style="text-align: center;">
                <a href="index.php" class="btn-add-cart" style="text-decoration: none; display: inline-flex; width: auto; padding: 0 40px; height: 50px; align-items: center; border-radius: 25px;">
                    Quay lại danh sách tin tức
                </a>
            </div>
        </div>
    </section>

    <?php include '../../components/footer.php'; ?>
    <?php include '../../components/snack-bar.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        $(document).ready(function() {
            const rawContent = $('#raw-markdown').val();
            if (rawContent) {
                $('#markdown-content').html(marked.parse(rawContent));
            } else {
                $('#markdown-content').html('<p style="color: grey; font-style: italic;">Nội dung bài viết đang được cập nhật...</p>');
            }
        });
    </script>

    <style>
    .markdown-body h1, .markdown-body h2, .markdown-body h3 {
        color: var(--primary-green);
        margin-top: 35px;
        margin-bottom: 20px;
    }
    .markdown-body p {
        margin-bottom: 20px;
    }
    .markdown-body img {
        max-width: 100%;
        border-radius: 12px;
        margin: 20px 0;
        box-shadow: var(--shadow);
    }
    .markdown-body ul, .markdown-body ol {
        margin-bottom: 20px;
        padding-left: 25px;
    }
    .markdown-body blockquote {
        border-left: 5px solid var(--primary-green);
        padding: 10px 20px;
        background-color: rgba(26, 77, 46, 0.05);
        color: var(--text-dark);
        font-style: italic;
        margin: 30px 0;
        border-radius: 4px;
    }
    </style>
</body>
</html>
