<?php

/**
 * The sidebar containing the main widget area
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area">
    <?php if (! quill_is_amp()) : ?>
        <?php quill_adsense_ad('sidebar'); ?>
    <?php endif; ?>

    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>