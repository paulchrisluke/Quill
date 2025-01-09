<?php

/**
 * Template part for displaying ad zones
 *
 * @package Quill
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

// Get AdSense settings
$settings = quill_get_adsense_settings();

// Check if we have a publisher ID
if (empty($settings['publisher_id'])) {
    return;
}

// Get the current ad zone
$zone = get_query_var('ad_zone', '');
if (empty($zone) || empty($settings['slots'][$zone])) {
    return;
}

// Get the ad slot ID
$slot_id = $settings['slots'][$zone];

// Output the ad based on whether we're in AMP mode or not
if (quill_is_amp()) {
    printf(
        '<div class="ad-zone %1$s"><amp-ad width="100vw" height="320" type="adsense" data-ad-client="ca-%2$s" data-ad-slot="%3$s" data-auto-format="rspv" data-full-width><div overflow></div></amp-ad></div>',
        esc_attr($zone),
        esc_attr($settings['publisher_id']),
        esc_attr($slot_id)
    );
} else {
    printf(
        '<div class="ad-zone %1$s"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-%2$s" data-ad-slot="%3$s" data-ad-format="auto" data-full-width-responsive="true"></ins></div>',
        esc_attr($zone),
        esc_attr($settings['publisher_id']),
        esc_attr($slot_id)
    );

    // Add the AdSense script if not already added
    if (! wp_script_is('google-adsense', 'enqueued')) {
        printf(
            '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-%s" crossorigin="anonymous"></script>',
            esc_attr($settings['publisher_id'])
        );
        wp_enqueue_script('google-adsense');
    }

    // Add the ad initialization script
    echo '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
}
