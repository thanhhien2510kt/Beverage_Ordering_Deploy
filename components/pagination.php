<?php
if (!isset($page)) $page = 1;
if (!isset($totalPages)) $totalPages = 1;
if (!isset($queryParams)) $queryParams = [];
if (!isset($ajaxMode)) $ajaxMode = false;


if (!isset($baseUrl)) {
    $baseUrl = $_SERVER['PHP_SELF'];
}


if (!function_exists('buildPaginationUrl')) {
    function buildPaginationUrl($pageNum, $baseUrl, $queryParams) {
        $params = array_merge($queryParams, ['page' => $pageNum]);
        $queryString = http_build_query($params);
        return $baseUrl . '?' . $queryString;
    }
}


$startPage = max(1, $page - 2);
$endPage = min($totalPages, $page + 2);
?>

<div class="pagination"<?php echo $ajaxMode ? ' data-ajax="1"' : ''; ?>>
    <?php if ($page > 1): ?>
        <?php if ($ajaxMode): ?>
            <button type="button" class="btn btn-outline pagination-btn" data-page="<?php echo (int)($page - 1); ?>" style="border-radius: 30px; height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px; line-height: 1; width: auto;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                <span>Trước</span>
            </button>
        <?php else: ?>
            <?php 
                $text = 'Trước';
                $type = 'outline';
                $href = buildPaginationUrl($page - 1, $baseUrl, $queryParams);
                $class = 'pagination-btn';
                $icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>';
                $iconPosition = 'left';
                $width = 'auto';
                include __DIR__ . '/button.php';
            ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="pagination-numbers">
        <?php if ($startPage > 1): ?>
            <?php if ($ajaxMode): ?>
                <button type="button" class="pagination-number" data-page="1">1</button>
            <?php else: ?>
                <a href="<?php echo buildPaginationUrl(1, $baseUrl, $queryParams); ?>" class="pagination-number">1</a>
            <?php endif; ?>
            <?php if ($startPage > 2): ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <?php if ($ajaxMode): ?>
                <button type="button" class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></button>
            <?php else: ?>
                <a href="<?php echo buildPaginationUrl($i, $baseUrl, $queryParams); ?>" 
                   class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>
            <?php if ($ajaxMode): ?>
                <button type="button" class="pagination-number" data-page="<?php echo $totalPages; ?>"><?php echo $totalPages; ?></button>
            <?php else: ?>
                <a href="<?php echo buildPaginationUrl($totalPages, $baseUrl, $queryParams); ?>" class="pagination-number"><?php echo $totalPages; ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($page < $totalPages): ?>
        <?php if ($ajaxMode): ?>
            <button type="button" class="btn btn-outline pagination-btn" data-page="<?php echo (int)($page + 1); ?>" style="border-radius: 30px; height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px; line-height: 1; width: auto;">
                <span>Sau</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        <?php else: ?>
            <?php 
                $text = 'Sau';
                $type = 'outline';
                $href = buildPaginationUrl($page + 1, $baseUrl, $queryParams);
                $class = 'pagination-btn';
                $icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>';
                $iconPosition = 'right';
                $width = 'auto';
                include __DIR__ . '/button.php';
            ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
