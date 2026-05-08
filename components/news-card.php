<?php
if (!isset($news)) return;

require_once __DIR__ . '/../functions.php';

$newsTitle = e($news['tieude']);
$newsId = $news['manews'];
$newsDate = !empty($news['ngaytao']) ? date('d', strtotime($news['ngaytao'])) : '24';
$newsMonth = !empty($news['ngaytao']) ? date('M', strtotime($news['ngaytao'])) : 'THG 12';


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


$imagePath = !empty($news['hinhanh']) ? $news['hinhanh'] : 'assets/img/news/news_one.jpg';
$imagePath = ltrim($imagePath, '/\\');
$newsImage = $basePath . $imagePath;


$markdownPath = !empty($news['noidung']) ? $news['noidung'] : '';
$newsExcerpt = !empty($markdownPath) ? getNewsExcerpt($markdownPath, 100) : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...';


$newsLink = $basePath . 'pages/news/detail.php?id=' . $newsId;
?>
<div class="news-card">
    <div class="news-image-wrapper">
        <a href="<?php echo e($newsLink); ?>">
            <img src="<?php echo e($newsImage); ?>" alt="<?php echo $newsTitle; ?>" class="news-image">
        </a>
        <div class="news-date-badge">
            <span class="date-day"><?php echo $newsDate; ?></span>
            <span class="date-month"><?php echo $newsMonth; ?></span>
        </div>
    </div>
    <div class="news-content">
        <h3 class="news-title">
            <a href="<?php echo e($newsLink); ?>"><?php echo $newsTitle; ?></a>
        </h3>
        <p class="news-excerpt"><?php echo e($newsExcerpt); ?></p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
            <a href="<?php echo e($newsLink); ?>" class="news-read-more">
                Xem chi tiết
            </a>
            <a href="javascript:void(0)" class="news-read-more btn-quick-view" data-news-id="<?php echo $newsId; ?>">
                Đọc nhanh →
            </a>
        </div>
    </div>
</div>
