<?php
if (!isset($href)) $href = "#top";
if (!isset($text)) $text = "Lên đầu trang";
if (!isset($bgType)) $bgType = "transparent";


$validBgTypes = ['light-green', 'white', 'transparent'];
if (!in_array($bgType, $validBgTypes)) {
    $bgType = 'transparent';
}


$bgClass = $bgType !== 'transparent' ? 'bg-' . $bgType : '';
$classAttr = 'back-to-top' . ($bgClass ? ' ' . $bgClass : '');
?>

<div class="<?php echo e($classAttr); ?>">
    <a href="#top" class="back-to-top-link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 15l-6-6-6 6"/>
        </svg>
        <span>Lên đầu trang</span>
    </a>
</div>
