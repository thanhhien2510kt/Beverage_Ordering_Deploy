<?php
if (!isset($news)) return;

require_once __DIR__ . '/../functions.php';

$newsTitle = e($news['TieuDe']);
$newsId = $news['MaNews'];
$newsDate = !empty($news['NgayTao']) ? date('d', strtotime($news['NgayTao'])) : '24';
$newsMonth = !empty($news['NgayTao']) ? date('M', strtotime($news['NgayTao'])) : 'THG 12';


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


$imagePath = !empty($news['HinhAnh']) ? $news['HinhAnh'] : 'assets/img/news/news_one.jpg';
$imagePath = ltrim($imagePath, '/\\');
$newsImage = $basePath . $imagePath;


$markdownPath = !empty($news['NoiDung']) ? $news['NoiDung'] : '';
$newsExcerpt = !empty($markdownPath) ? getNewsExcerpt($markdownPath, 100) : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...';


$newsLink = $basePath . 'pages/news/index.php#news-' . $newsId;
?>
<div class="news-card">
    <div class="news-image-wrapper">
        <img src="<?php echo e($newsImage); ?>" alt="<?php echo $newsTitle; ?>" class="news-image">
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
        <a href="<?php echo e($newsLink); ?>" class="news-read-more">
            Đọc tiếp →
        </a>
    </div>
</div>
