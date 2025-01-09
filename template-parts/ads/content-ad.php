<?php

/**
 * Template part for displaying AMP ads
 *
 * @package Quill
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$ad_client = get_theme_mod('quill_adsense_client');
$ad_slot = get_theme_mod('quill_adsense_slot_' . $args['location']);

if (empty($ad_client) || empty($ad_slot)) {
    return;
}
?>

<div class="ad-container">
    <amp-ad
        type="adsense"
        width="100vw"
        height="320"
        data-ad-client="<?php echo esc_attr($ad_client); ?>"
        data-ad-slot="<?php echo esc_attr($ad_slot); ?>"
        data-auto-format="rspv"
        data-full-width="">
        <div overflow=""></div>
    </amp-ad>
</div>