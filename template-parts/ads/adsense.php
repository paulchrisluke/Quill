<?php

/**
 * Template part for displaying AdSense containers
 *
 * @package Quill
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$ad_position = isset($args['position']) ? $args['position'] : 'content';
$ad_class = 'ad-container ad-' . $ad_position;
?>

<div class="<?php echo esc_attr($ad_class); ?>">
    <amp-ad
        type="adsense"
        layout="responsive"
        data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id', '')); ?>"
        <?php if ($ad_position === 'header'): ?>
        width="728"
        height="90"
        data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_header_slot', '')); ?>"
        <?php elseif ($ad_position === 'content'): ?>
        width="336"
        height="280"
        data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_content_slot', '')); ?>"
        <?php elseif ($ad_position === 'sidebar'): ?>
        width="300"
        height="250"
        data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_sidebar_slot', '')); ?>"
        <?php endif; ?>>
    </amp-ad>
</div>