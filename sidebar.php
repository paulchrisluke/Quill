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
    <?php get_template_part('template-parts/ads/adsense', 'sidebar'); ?>
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>