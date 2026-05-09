<?php
// Favicon & PWA meta tags — included in <head> of every page
// Detect base path relative to docroot
$depth = substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 1;
$base  = $depth > 0 ? str_repeat('../', $depth) : './';
?>
<link rel="icon" type="image/svg+xml" href="<?= $base ?>assets/img/favicon.svg">
<link rel="icon" type="image/x-icon" href="<?= $base ?>assets/img/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>assets/img/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $base ?>assets/img/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $base ?>assets/img/apple-touch-icon.png">
<meta name="theme-color" content="#11331e">
