<?php

/**
 * Quill functions and definitions
 *
 * @package Quill
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('QUILL_VERSION', '1.0.0');
define('QUILL_DIR', get_template_directory());
define('QUILL_URI', get_template_directory_uri());

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function quill_setup()
{
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // This theme is AMP-first
    add_theme_support('amp', array(
        'paired' => false,
        'templates_supported' => 'all',
        'comments_live_list' => true,
    ));

    // Register nav menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'quill'),
        'footer' => __('Footer Menu', 'quill'),
    ));
}
add_action('after_setup_theme', 'quill_setup');

/**
 * Register the recipe post type
 */
function quill_register_recipe_post_type()
{
    $labels = array(
        'name' => _x('Recipes', 'post type general name', 'quill'),
        'singular_name' => _x('Recipe', 'post type singular name', 'quill'),
        'menu_name' => _x('Recipes', 'admin menu', 'quill'),
        'add_new' => _x('Add New', 'recipe', 'quill'),
        'add_new_item' => __('Add New Recipe', 'quill'),
        'new_item' => __('New Recipe', 'quill'),
        'edit_item' => __('Edit Recipe', 'quill'),
        'view_item' => __('View Recipe', 'quill'),
        'all_items' => __('All Recipes', 'quill'),
        'search_items' => __('Search Recipes', 'quill'),
        'not_found' => __('No recipes found.', 'quill'),
        'not_found_in_trash' => __('No recipes found in Trash.', 'quill')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'recipe'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest' => true,
    );

    register_post_type('recipe', $args);
}
add_action('init', 'quill_register_recipe_post_type');

/**
 * Handle WordPress styles for AMP compatibility
 */
function quill_handle_styles()
{
    // Remove default styles that might conflict
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('global-styles');

    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('wp_enqueue_scripts', 'quill_handle_styles', 999);

/**
 * Buffer the wp_head output to modify style tags
 */
function quill_buffer_start()
{
    ob_start();
}
add_action('wp_head', 'quill_buffer_start', 0);

function quill_buffer_end()
{
    $html = ob_get_clean();

    // Extract and combine all custom styles
    $styles = '';
    if (preg_match_all('/<style(?![^>]*amp-boilerplate)[^>]*>(.*?)<\/style>/s', $html, $matches)) {
        foreach ($matches[1] as $style) {
            $styles .= $style . "\n";
        }
        // Remove all style tags except boilerplate
        $html = preg_replace('/<style(?![^>]*amp-boilerplate)[^>]*>.*?<\/style>/s', '', $html);
    }

    // Add combined styles back with amp-custom attribute, but only if not already present
    if (!empty($styles) && !strpos($html, 'amp-custom')) {
        $styles = '<style amp-custom>' . $styles . '</style>';
        // Insert after last style tag or before </head>
        if (strpos($html, '</style>') !== false) {
            $html = preg_replace('/<\/style>(?!.*<\/style>)/s', '</style>' . $styles, $html);
        } else {
            $html = str_replace('</head>', $styles . '</head>', $html);
        }
    }

    echo $html;
}
add_action('wp_head', 'quill_buffer_end', 999);

/**
 * Convert embeds to AMP components
 */
function quill_convert_embeds_to_amp($content)
{
    // Convert YouTube embeds
    $content = preg_replace(
        '/<iframe[^>]*youtube\.com\/embed\/([a-zA-Z0-9_-]+)[^>]*><\/iframe>/',
        '<amp-youtube data-videoid="$1" layout="responsive" width="480" height="270"></amp-youtube>',
        $content
    );

    // Convert Google Maps embeds
    $content = preg_replace_callback(
        '/<iframe[^>]*src="([^"]*google\.com\/maps[^"]*)"[^>]*><\/iframe>/',
        function ($matches) {
            return '<amp-iframe src="' . esc_url($matches[1]) . '" 
                width="600" 
                height="400" 
                layout="responsive" 
                sandbox="allow-scripts allow-same-origin allow-popups" 
                frameborder="0">
                <amp-img layout="fill" 
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" 
                    placeholder>
                </amp-img>
            </amp-iframe>';
        },
        $content
    );

    return $content;
}
add_filter('the_content', 'quill_convert_embeds_to_amp', 20);

/**
 * Convert images to amp-img
 */
function quill_amp_img_attributes($attributes)
{
    if (empty($attributes['src'])) {
        return $attributes;
    }

    // Get image dimensions if not set
    if (empty($attributes['width']) || empty($attributes['height'])) {
        $image_path = str_replace(home_url(), ABSPATH, $attributes['src']);
        if (file_exists($image_path)) {
            list($width, $height) = getimagesize($image_path);
            $attributes['width'] = $width;
            $attributes['height'] = $height;
        } else {
            // Default dimensions if we can't get the actual ones
            $attributes['width'] = 600;
            $attributes['height'] = 400;
        }
    }

    // Add layout attribute if not present
    if (empty($attributes['layout'])) {
        $attributes['layout'] = 'responsive';
    }

    return $attributes;
}
add_filter('amp_img_attributes', 'quill_amp_img_attributes');

/**
 * Register theme customizer settings for ads
 */
function quill_customize_register($wp_customize)
{
    // Add section for ad settings
    $wp_customize->add_section('quill_ads', array(
        'title' => __('Ad Settings', 'quill'),
        'priority' => 120,
    ));

    // Add AdSense Client ID setting
    $wp_customize->add_setting('quill_adsense_client', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('quill_adsense_client', array(
        'label' => __('AdSense Client ID', 'quill'),
        'section' => 'quill_ads',
        'type' => 'text',
    ));

    // Add ad slot settings for different locations
    $ad_locations = array(
        'header' => __('Header Ad Slot', 'quill'),
        'content' => __('Content Ad Slot', 'quill'),
        'sidebar' => __('Sidebar Ad Slot', 'quill'),
        'footer' => __('Footer Ad Slot', 'quill'),
    );

    foreach ($ad_locations as $location => $label) {
        $wp_customize->add_setting('quill_adsense_slot_' . $location, array(
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control('quill_adsense_slot_' . $location, array(
            'label' => $label,
            'section' => 'quill_ads',
            'type' => 'text',
        ));
    }
}
add_action('customize_register', 'quill_customize_register');

/**
 * Display ad in a specific location
 */
function quill_display_ad($location)
{
    get_template_part('template-parts/ads/content', 'ad', array('location' => $location));
}

/**
 * Fetch media details from tiffycooks.com REST API
 */
function quill_get_media_details($attachment_id)
{
    $api_url = 'https://tiffycooks.com/wp-json/wp/v2/media/' . $attachment_id;
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return false;
    }

    $media = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($media) || !isset($media['source_url'])) {
        return false;
    }

    // Get the URL and ensure it's HTTPS
    $url = $media['source_url'];

    // Handle CDN URLs if present
    if (isset($media['media_details']['sizes']['full']['source_url'])) {
        $url = $media['media_details']['sizes']['full']['source_url'];
    }

    // Force HTTPS
    $url = str_replace('http://', 'https://', $url);

    // Get dimensions
    $width = 600;
    $height = 400;

    if (isset($media['media_details']['width']) && isset($media['media_details']['height'])) {
        $width = $media['media_details']['width'];
        $height = $media['media_details']['height'];
    } elseif (isset($media['media_details']['sizes']['full'])) {
        $width = $media['media_details']['sizes']['full']['width'];
        $height = $media['media_details']['sizes']['full']['height'];
    }

    return array(
        'url' => $url,
        'width' => $width,
        'height' => $height,
        'alt' => $media['alt_text'] ?? ''
    );
}

/**
 * Fix malformed URLs in content
 */
function quill_fix_urls($content)
{
    // Fix URLs with spaces
    $content = preg_replace_callback(
        '/<a[^>]*href=([\'"])(.*?)\1[^>]*>/i',
        function ($matches) {
            $url = $matches[2];
            // Replace spaces with hyphens
            $url = str_replace(' ', '-', $url);
            // Ensure proper URL encoding
            $url = esc_url($url);
            return str_replace($matches[2], $url, $matches[0]);
        },
        $content
    );

    return $content;
}
add_filter('the_content', 'quill_fix_urls', 5);
add_filter('widget_text_content', 'quill_fix_urls', 5);

/**
 * Print minimal AMP-compatible styles
 */
function quill_print_amp_styles()
{
    // Minimal required styles for AMP compatibility
    $styles = '
        /* Basic AMP styles */
        amp-img {
            max-width: 100%;
            height: auto;
        }
        /* Ad container styles */
        .ad-container {
            background-color: #e8f5e9;
            border: 2px dashed #4caf50;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    ';

    echo '<style amp-custom>' . trim($styles) . '</style>';
}

// Load required files
require_once QUILL_DIR . '/includes/template-tags.php';
require_once QUILL_DIR . '/includes/customizer/class-quill-customizer.php';
