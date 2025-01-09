<?php

/**
 * AMP-related functions
 *
 * @package Quill
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Check if the current request is for an AMP page
 *
 * @return bool True if AMP, false otherwise
 */
function quill_is_amp()
{
    // Check if the official AMP plugin is active
    if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
        return true;
    }

    // Check URL-based detection (for other AMP plugins)
    $is_amp = false;

    // Check for ?amp query var
    if (isset($_GET['amp']) && $_GET['amp'] === '1') {
        $is_amp = true;
    }

    // Check for /amp/ in permalink
    if (strpos($_SERVER['REQUEST_URI'], '/amp/') !== false) {
        $is_amp = true;
    }

    // Check for ?amp=1 in permalink
    if (strpos($_SERVER['REQUEST_URI'], '?amp=1') !== false) {
        $is_amp = true;
    }

    /**
     * Filter whether the current request is for an AMP page
     *
     * @param bool $is_amp Whether the current request is for an AMP page
     */
    return apply_filters('quill_is_amp', $is_amp);
}
