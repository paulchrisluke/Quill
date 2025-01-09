<?php

/**
 * Bootstrap file for initializing theme features
 *
 * @package Quill
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
if (!defined('QUILL_VERSION')) {
    define('QUILL_VERSION', '1.0.0');
}

if (!defined('QUILL_DIR')) {
    define('QUILL_DIR', get_template_directory());
}

if (!defined('QUILL_URI')) {
    define('QUILL_URI', get_template_directory_uri());
}

// Disable WordPress features that conflict with AMP
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_resource_hints', 2);
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

// Remove all default scripts and styles that conflict with AMP
function quill_remove_default_assets()
{
    // Remove scripts
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_head', 'wp_resource_hints', 2);
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');

    // Remove styles
    remove_action('wp_head', 'wp_print_styles', 8);
    remove_action('wp_head', 'wp_enqueue_style', 8);
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Remove jQuery and wp-embed
    wp_dequeue_script('jquery');
    wp_deregister_script('jquery');
    wp_dequeue_script('wp-embed');
    wp_deregister_script('wp-embed');
}
add_action('init', 'quill_remove_default_assets');

// Force HTTPS for all URLs
function quill_force_https($url)
{
    if (is_ssl()) {
        $url = str_replace('http://', 'https://', $url);
    }
    return $url;
}

// Apply HTTPS filter to all relevant URLs
add_filter('plugins_url', 'quill_force_https');
add_filter('theme_root_uri', 'quill_force_https');
add_filter('stylesheet_uri', 'quill_force_https');
add_filter('template_directory_uri', 'quill_force_https');
add_filter('script_loader_src', 'quill_force_https');
add_filter('style_loader_src', 'quill_force_https');

// Load theme files
require_once QUILL_DIR . '/includes/class-quill.php';
require_once QUILL_DIR . '/includes/template-tags.php';
require_once QUILL_DIR . '/includes/customizer/class-quill-customizer.php';

// Initialize theme
function quill_init()
{
    // Initialize main theme class
    $quill = Quill::get_instance();
    $quill->init();

    // Initialize customizer
    if (class_exists('Quill_Customizer')) {
        $customizer = new Quill_Customizer();
        $customizer->init();
    }
}
add_action('after_setup_theme', 'quill_init');
