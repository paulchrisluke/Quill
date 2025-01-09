<?php

/**
 * Template functions and definitions
 *
 * @package Quill
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load required WordPress core files
 */
require_once get_template_directory() . '/includes/bootstrap.php';

/**
 * Load template tags
 */
require_once get_template_directory() . '/includes/template-tags.php';

/**
 * Ensure all required WordPress functions are available
 */
if (!function_exists('wp_body_open')) {
    function wp_body_open()
    {
        do_action('wp_body_open');
    }
}

/**
 * Get AdSense settings
 */
function quill_get_adsense_settings()
{
    $defaults = array(
        'publisher_id' => '',
        'top_ad_slot' => '',
        'middle_ad_slot' => '',
        'bottom_ad_slot' => '',
    );

    return wp_parse_args(get_theme_mod('quill_adsense_settings', array()), $defaults);
}

/**
 * Register required styles
 */
function quill_register_assets()
{
    // Register styles
    wp_register_style(
        'quill-amp-styles',
        get_template_directory_uri() . '/assets/css/amp.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'quill_register_assets');

/**
 * Add custom image sizes
 */
function quill_add_image_sizes()
{
    add_image_size('recipe-featured', 1200, 800, true);
    add_image_size('recipe-schema', 1200, 1200, true);
}
add_action('after_setup_theme', 'quill_add_image_sizes');

/**
 * Register recipe post type
 */
function quill_register_recipe_post_type()
{
    $labels = array(
        'name'               => _x('Recipes', 'post type general name', 'quill'),
        'singular_name'      => _x('Recipe', 'post type singular name', 'quill'),
        'menu_name'          => _x('Recipes', 'admin menu', 'quill'),
        'add_new'            => _x('Add New', 'recipe', 'quill'),
        'add_new_item'       => __('Add New Recipe', 'quill'),
        'new_item'           => __('New Recipe', 'quill'),
        'edit_item'          => __('Edit Recipe', 'quill'),
        'view_item'          => __('View Recipe', 'quill'),
        'all_items'          => __('All Recipes', 'quill'),
        'search_items'       => __('Search Recipes', 'quill'),
        'not_found'          => __('No recipes found.', 'quill'),
        'not_found_in_trash' => __('No recipes found in Trash.', 'quill')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'recipe'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'       => true,
    );

    register_post_type('recipe', $args);
}
add_action('init', 'quill_register_recipe_post_type');
