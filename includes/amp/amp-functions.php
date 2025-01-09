<?php

/**
 * AMP-specific functions and definitions
 *
 * @package Quill
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize AMP support
 */
function quill_init_amp()
{
    // Remove default WordPress actions that might interfere with AMP
    remove_action('wp_head', 'wp_print_styles');
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Add AMP hooks
    add_filter('script_loader_tag', 'quill_amp_script_loader_tag', 10, 3);
}
add_action('init', 'quill_init_amp');

/**
 * Modify script tags for AMP compatibility
 */
function quill_amp_script_loader_tag($tag, $handle, $src)
{
    // Remove any non-AMP scripts
    if (!strpos($src, 'ampproject.org')) {
        return '';
    }
    return $tag;
}

/**
 * Add AMP-specific body classes
 */
function quill_amp_body_class($classes)
{
    $classes[] = 'amp';
    if (is_singular('recipe')) {
        $classes[] = 'amp-recipe';
    }
    return $classes;
}
add_filter('body_class', 'quill_amp_body_class');

/**
 * Modify AMP metadata for recipes
 */
function quill_amp_recipe_metadata($metadata)
{
    if (is_singular('recipe')) {
        $post_id = get_the_ID();

        // Add recipe-specific schema
        $metadata['@type'] = 'Recipe';

        // Add cooking time
        $prep_time = get_post_meta($post_id, '_recipe_prep_time', true);
        $cook_time = get_post_meta($post_id, '_recipe_cook_time', true);
        $total_time = get_post_meta($post_id, '_recipe_total_time', true);

        if ($prep_time) {
            $metadata['prepTime'] = $prep_time;
        }
        if ($cook_time) {
            $metadata['cookTime'] = $cook_time;
        }
        if ($total_time) {
            $metadata['totalTime'] = $total_time;
        }

        // Add recipe ingredients
        $ingredients = get_post_meta($post_id, '_recipe_ingredients', true);
        if (!empty($ingredients) && is_array($ingredients)) {
            $metadata['recipeIngredient'] = array_map('wp_strip_all_tags', $ingredients);
        }

        // Add recipe instructions
        $instructions = get_post_meta($post_id, '_recipe_instructions', true);
        if (!empty($instructions) && is_array($instructions)) {
            $metadata['recipeInstructions'] = array();
            foreach ($instructions as $index => $step) {
                $metadata['recipeInstructions'][] = array(
                    '@type' => 'HowToStep',
                    'position' => $index + 1,
                    'text' => wp_strip_all_tags($step),
                );
            }
        }

        // Add nutrition information
        $nutrition = get_post_meta($post_id, '_recipe_nutrition', true);
        if (!empty($nutrition) && is_array($nutrition)) {
            $metadata['nutrition'] = array(
                '@type' => 'NutritionInformation',
            );

            $nutrition_mapping = array(
                'calories' => 'calories',
                'protein' => 'proteinContent',
                'carbohydrates' => 'carbohydrateContent',
                'fat' => 'fatContent',
                'fiber' => 'fiberContent',
                'sugar' => 'sugarContent',
            );

            foreach ($nutrition_mapping as $key => $schema_key) {
                if (isset($nutrition[$key])) {
                    $metadata['nutrition'][$schema_key] = $nutrition[$key];
                }
            }
        }
    }

    return $metadata;
}
add_filter('amp_post_template_metadata', 'quill_amp_recipe_metadata');

/**
 * Force HTTPS for all AMP URLs
 */
function quill_amp_force_https($url)
{
    return is_ssl() ? str_replace('http://', 'https://', $url) : $url;
}
add_filter('amp_link_rel_canonical_url', 'quill_amp_force_https');
add_filter('amp_site_icon_url', 'quill_amp_force_https');
add_filter('amp_post_template_url', 'quill_amp_force_https');
