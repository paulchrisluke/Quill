<?php
if (!defined('ABSPATH')) {
    exit;
}

// Remove unnecessary header output
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// Basic theme setup
function quill_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'quill'),
    ));
}
add_action('after_setup_theme', 'quill_setup');

// AMP Nav Walker
class Quill_AMP_Nav_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $output .= '<li class="menu-item">';
        $output .= '<a href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = array())
    {
        $output .= '</li>';
    }
}

// Content filter for AMP compliance
function quill_amp_content_filter($content)
{
    // Fix malformed URLs in links
    $content = preg_replace_callback('/<a([^>]*?)href=[\'"](.*?)[\'"](.*?)>/i', function ($matches) {
        $attrs = $matches[1] . $matches[3];
        $url = $matches[2];

        // Remove spaces from URLs
        $url = str_replace(' ', '-', $url);

        return sprintf(
            '<a%shref="%s"%s>',
            $matches[1],
            esc_url($url),
            $matches[3]
        );
    }, $content);

    // Convert iframes to amp-iframe
    $content = preg_replace_callback('/<iframe([^>]*)>.*?<\/iframe>/is', function ($matches) {
        $attrs = $matches[1];

        // Extract src
        preg_match('/src=[\'"](.*?)[\'"]/', $attrs, $src);
        $src = isset($src[1]) ? $src[1] : '';

        // Extract width and height
        preg_match('/width=[\'"](.*?)[\'"]/', $attrs, $width);
        preg_match('/height=[\'"](.*?)[\'"]/', $attrs, $height);

        // Convert percentage width to pixels
        $width = isset($width[1]) ? $width[1] : '600';
        if (strpos($width, '%') !== false) {
            $width = '600';
        }

        $height = isset($height[1]) ? $height[1] : '400';
        if (strpos($height, '%') !== false) {
            $height = '400';
        }

        return sprintf(
            '<amp-iframe width="%s" height="%s" layout="responsive" sandbox="allow-scripts allow-same-origin" src="%s"><noscript><iframe src="%s" width="%s" height="%s"></iframe></noscript></amp-iframe>',
            esc_attr($width),
            esc_attr($height),
            esc_url($src),
            esc_url($src),
            esc_attr($width),
            esc_attr($height)
        );
    }, $content);

    // Convert img to amp-img
    $content = preg_replace_callback('/<img([^>]+)>/i', function ($matches) {
        $attrs = $matches[1];

        // Extract src, width, and height
        preg_match('/src=[\'"](.*?)[\'"]/', $attrs, $src);
        preg_match('/width=[\'"](.*?)[\'"]/', $attrs, $width);
        preg_match('/height=[\'"](.*?)[\'"]/', $attrs, $height);
        preg_match('/alt=[\'"](.*?)[\'"]/', $attrs, $alt);

        $src = isset($src[1]) ? $src[1] : '';
        $width = isset($width[1]) ? $width[1] : '600';
        $height = isset($height[1]) ? $height[1] : '400';
        $alt = isset($alt[1]) ? $alt[1] : '';

        // Convert percentage width to pixels
        if (strpos($width, '%') !== false) {
            $width = '600';
        }

        return sprintf(
            '<amp-img src="%s" width="%s" height="%s" alt="%s" layout="responsive"></amp-img>',
            esc_url($src),
            esc_attr($width),
            esc_attr($height),
            esc_attr($alt)
        );
    }, $content);

    return $content;
}
add_filter('the_content', 'quill_amp_content_filter', 20);

// Remove all default WordPress styles and scripts
function quill_remove_all_scripts()
{
    global $wp_scripts;
    $wp_scripts->queue = array();
}
add_action('wp_print_scripts', 'quill_remove_all_scripts', 100);

function quill_remove_all_styles()
{
    global $wp_styles;
    $wp_styles->queue = array();
}
add_action('wp_print_styles', 'quill_remove_all_styles', 100);

// Disable admin bar
add_filter('show_admin_bar', '__return_false');

// Remove jQuery
function quill_remove_jquery()
{
    wp_deregister_script('jquery');
}
add_action('wp_enqueue_scripts', 'quill_remove_jquery');
