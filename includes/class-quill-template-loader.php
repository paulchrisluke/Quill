<?php

/**
 * Template Loader for AMP pages
 *
 * @package Quill
 */

if (!defined('ABSPATH')) {
    exit;
}

class Quill_Template_Loader
{
    /**
     * Start buffering and load template
     */
    public function __construct()
    {
        add_action('template_redirect', array($this, 'buffer_start'));
        add_filter('template_include', array($this, 'load_template'));
    }

    /**
     * Start output buffering
     */
    public function buffer_start()
    {
        ob_start(array($this, 'process_output'));
    }

    /**
     * Process and clean the output
     */
    public function process_output($buffer)
    {
        // Remove any whitespace before DOCTYPE
        $buffer = preg_replace('/^[\s\n\r]+/', '', $buffer);

        // Ensure proper DOCTYPE
        if (strpos($buffer, '<!doctype html>') !== 0) {
            $buffer = '<!doctype html>' . PHP_EOL . $buffer;
        }

        // Fix HTML tag
        $buffer = preg_replace('/<html[^>]*>/', '<html ⚡ ' . get_language_attributes() . '>', $buffer);

        // Ensure meta charset is first in head
        if (preg_match('/<head[^>]*>(.*?)<\/head>/s', $buffer, $matches)) {
            $head_content = $matches[1];
            $new_head = '<meta charset="utf-8">' . PHP_EOL;
            $new_head .= '<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">' . PHP_EOL;
            $new_head .= '<script async src="https://cdn.ampproject.org/v0.js"></script>' . PHP_EOL;
            $new_head .= '<link rel="canonical" href="' . esc_url(get_permalink() ?: home_url('/')) . '">' . PHP_EOL;
            $new_head .= preg_replace('/<meta\s+charset=[^>]+>/', '', $head_content);
            $buffer = str_replace($head_content, $new_head, $buffer);
        }

        // Remove any non-AMP scripts
        $buffer = preg_replace('/<script(?![\s]*amp).*?<\/script>/s', '', $buffer);

        // Remove any non-AMP styles
        $buffer = preg_replace('/<link[^>]*rel=["\']stylesheet["\'][^>]*>/i', '', $buffer);

        // Ensure all images are AMP-compliant
        $buffer = preg_replace_callback('/<img([^>]+)>/i', function ($matches) {
            $attributes = $matches[1];

            // Extract existing width and height
            preg_match('/width=[\'"]?(\d+)[\'"]?/i', $attributes, $width_matches);
            preg_match('/height=[\'"]?(\d+)[\'"]?/i', $attributes, $height_matches);

            $width = isset($width_matches[1]) ? $width_matches[1] : '800';
            $height = isset($height_matches[1]) ? $height_matches[1] : '600';

            // Extract src
            preg_match('/src=[\'"]?([^\'" >]+)[\'"]?/i', $attributes, $src_matches);
            $src = $src_matches[1];

            return sprintf(
                '<amp-img src="%s" width="%s" height="%s" layout="responsive"></amp-img>',
                esc_url($src),
                esc_attr($width),
                esc_attr($height)
            );
        }, $buffer);

        return $buffer;
    }

    /**
     * Load the appropriate template
     */
    public function load_template($template)
    {
        // Remove all actions from wp_head and wp_footer
        remove_all_actions('wp_head');
        remove_all_actions('wp_footer');

        // Add back essential AMP elements
        add_action('wp_head', function () {
            // AMP boilerplate styles
            echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>';
            echo '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';

            // AMP components
            echo '<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>';
            echo '<script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>';
        }, 1);

        return $template;
    }
}
