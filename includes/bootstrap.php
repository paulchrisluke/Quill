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

// Remove all default scripts
remove_action('wp_head', 'wp_print_scripts');
remove_action('wp_head', 'wp_print_head_scripts', 9);
remove_action('wp_head', 'wp_enqueue_scripts', 1);

// Remove all default styles
remove_action('wp_head', 'wp_print_styles', 8);
remove_action('wp_head', 'wp_enqueue_style', 8);

// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Remove jQuery
function quill_deregister_scripts()
{
    wp_deregister_script('jquery');
    wp_deregister_script('wp-embed');
}
add_action('wp_enqueue_scripts', 'quill_deregister_scripts', 100);

// Force HTTPS for all URLs
function quill_force_https($url)
{
    if (is_ssl()) {
        $url = str_replace('http://', 'https://', $url);
    }
    return $url;
}
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
require_once QUILL_DIR . '/includes/amp/amp-functions.php';

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

// Ensure proper AMP initialization
function quill_after_setup_theme()
{
    // Remove theme support for features that conflict with AMP
    remove_theme_support('custom-background');
    remove_theme_support('custom-header');
    remove_theme_support('widgets-block-editor');

    // Add AMP-specific theme support
    add_theme_support('amp', array(
        'paired' => false,
        'templates_supported' => 'all',
        'comments_live_list' => true,
    ));
}
add_action('after_setup_theme', 'quill_after_setup_theme', 11);

// Modify wp_head priority to ensure AMP scripts load first
function quill_modify_wp_head_priority()
{
    remove_action('wp_head', 'wp_head', 1);
    add_action('wp_head', 'wp_head', 999);
}
add_action('init', 'quill_modify_wp_head_priority');

// Ensure proper loading order of styles and scripts
function quill_buffer_output()
{
    ob_start();
}
add_action('template_redirect', 'quill_buffer_output', 0);

function quill_flush_output()
{
    $content = ob_get_clean();

    // Move all scripts to head
    $content = preg_replace('/<script([^>]*)>(.*?)<\/script>/is', '', $content);

    // Move all styles to head
    $content = preg_replace('/<style([^>]*)>(.*?)<\/style>/is', '', $content);

    // Move all link tags to head
    $content = preg_replace('/<link([^>]*)>/is', '', $content);

    echo $content;
}
add_action('shutdown', 'quill_flush_output', 0);
