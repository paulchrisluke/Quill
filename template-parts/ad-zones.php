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

// Output AMP-compatible ad
printf(
    '<div class="ad-zone %1$s"><amp-ad width="100vw" height="320" type="adsense" data-ad-client="ca-%2$s" data-ad-slot="%3$s" data-auto-format="rspv" data-full-width><div overflow></div></amp-ad></div>',
    esc_attr($zone),
    esc_attr($settings['publisher_id']),
    esc_attr($slot_id)
);
