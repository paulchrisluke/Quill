<?php

/**
 * Custom template tags for this theme
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function quill_posted_on()
{
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    if (get_the_time('U') !== get_the_modified_time('U')) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf(
        $time_string,
        esc_attr(get_the_date('c')),
        esc_html(get_the_date()),
        esc_attr(get_the_modified_date('c')),
        esc_html(get_the_modified_date())
    );

    $posted_on = sprintf(
        /* translators: %s: post date */
        esc_html_x('Posted on %s', 'post date', 'quill'),
        '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
    );

    $byline = sprintf(
        /* translators: %s: post author */
        esc_html_x('by %s', 'post author', 'quill'),
        '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
    );

    echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function quill_entry_footer()
{
    // Hide category and tag text for pages.
    if ('post' === get_post_type()) {
        /* translators: used between list items, there is a space after the comma */
        $categories_list = get_the_category_list(esc_html__(', ', 'quill'));
        if ($categories_list) {
            /* translators: 1: list of categories. */
            printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'quill') . '</span>', $categories_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        /* translators: used between list items, there is a space after the comma */
        $tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'quill'));
        if ($tags_list) {
            /* translators: 1: list of tags. */
            printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'quill') . '</span>', $tags_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    if (! is_single() && ! post_password_required() && (comments_open() || get_comments_number())) {
        echo '<span class="comments-link">';
        comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __('Leave a Comment<span class="screen-reader-text"> on %s</span>', 'quill'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            )
        );
        echo '</span>';
    }

    edit_post_link(
        sprintf(
            wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                __('Edit <span class="screen-reader-text">%s</span>', 'quill'),
                array(
                    'span' => array(
                        'class' => array(),
                    ),
                )
            ),
            wp_kses_post(get_the_title())
        ),
        '<span class="edit-link">',
        '</span>'
    );
}

/**
 * Get recipe metadata
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return array Recipe metadata.
 */
function quill_get_recipe_meta($post = null)
{
    $post = get_post($post);
    if (! $post) {
        return array();
    }

    $meta = array(
        'prep_time'     => get_post_meta($post->ID, '_recipe_prep_time', true),
        'cook_time'     => get_post_meta($post->ID, '_recipe_cook_time', true),
        'total_time'    => get_post_meta($post->ID, '_recipe_total_time', true),
        'servings'      => get_post_meta($post->ID, '_recipe_servings', true),
        'calories'      => get_post_meta($post->ID, '_recipe_calories', true),
        'difficulty'    => get_post_meta($post->ID, '_recipe_difficulty', true),
        'cuisine'       => wp_get_post_terms($post->ID, 'cuisine', array('fields' => 'names')),
        'course'        => wp_get_post_terms($post->ID, 'course', array('fields' => 'names')),
        'dietary'       => wp_get_post_terms($post->ID, 'dietary', array('fields' => 'names')),
    );

    return apply_filters('quill_recipe_meta', $meta, $post);
}

/**
 * Get recipe ingredients
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return array Recipe ingredients.
 */
function quill_get_recipe_ingredients($post = null)
{
    $post = get_post($post);
    if (! $post) {
        return array();
    }

    $ingredients = get_post_meta($post->ID, '_recipe_ingredients', true);
    if (! $ingredients) {
        return array();
    }

    return apply_filters('quill_recipe_ingredients', $ingredients, $post);
}

/**
 * Get recipe instructions
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return array Recipe instructions.
 */
function quill_get_recipe_instructions($post = null)
{
    $post = get_post($post);
    if (! $post) {
        return array();
    }

    $instructions = get_post_meta($post->ID, '_recipe_instructions', true);
    if (! $instructions) {
        return array();
    }

    return apply_filters('quill_recipe_instructions', $instructions, $post);
}

/**
 * Get recipe notes
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return string Recipe notes.
 */
function quill_get_recipe_notes($post = null)
{
    $post = get_post($post);
    if (! $post) {
        return '';
    }

    $notes = get_post_meta($post->ID, '_recipe_notes', true);
    if (! $notes) {
        return '';
    }

    return apply_filters('quill_recipe_notes', $notes, $post);
}

/**
 * Get recipe nutrition information
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return array Recipe nutrition information.
 */
function quill_get_recipe_nutrition($post = null)
{
    $post = get_post($post);
    if (! $post) {
        return array();
    }

    $nutrition = array(
        'calories'      => get_post_meta($post->ID, '_recipe_calories', true),
        'fat'           => get_post_meta($post->ID, '_recipe_fat', true),
        'saturated_fat' => get_post_meta($post->ID, '_recipe_saturated_fat', true),
        'cholesterol'   => get_post_meta($post->ID, '_recipe_cholesterol', true),
        'sodium'        => get_post_meta($post->ID, '_recipe_sodium', true),
        'carbohydrates' => get_post_meta($post->ID, '_recipe_carbohydrates', true),
        'fiber'         => get_post_meta($post->ID, '_recipe_fiber', true),
        'sugar'         => get_post_meta($post->ID, '_recipe_sugar', true),
        'protein'       => get_post_meta($post->ID, '_recipe_protein', true),
    );

    return apply_filters('quill_recipe_nutrition', $nutrition, $post);
}

/**
 * Display recipe metadata
 *
 * @param WP_Post|int|null $post Post object or ID.
 */
function quill_recipe_meta($post = null)
{
    $meta = quill_get_recipe_meta($post);
    if (empty($meta)) {
        return;
    }

    $output = '<div class="recipe-meta">';

    if (! empty($meta['prep_time'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item prep-time"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Prep Time:', 'quill'),
            esc_html($meta['prep_time'])
        );
    }

    if (! empty($meta['cook_time'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item cook-time"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Cook Time:', 'quill'),
            esc_html($meta['cook_time'])
        );
    }

    if (! empty($meta['total_time'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item total-time"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Total Time:', 'quill'),
            esc_html($meta['total_time'])
        );
    }

    if (! empty($meta['servings'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item servings"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Servings:', 'quill'),
            esc_html($meta['servings'])
        );
    }

    if (! empty($meta['calories'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item calories"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Calories:', 'quill'),
            esc_html($meta['calories'])
        );
    }

    if (! empty($meta['difficulty'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item difficulty"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Difficulty:', 'quill'),
            esc_html($meta['difficulty'])
        );
    }

    if (! empty($meta['cuisine'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item cuisine"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Cuisine:', 'quill'),
            esc_html(implode(', ', $meta['cuisine']))
        );
    }

    if (! empty($meta['course'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item course"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Course:', 'quill'),
            esc_html(implode(', ', $meta['course']))
        );
    }

    if (! empty($meta['dietary'])) {
        $output .= sprintf(
            '<div class="recipe-meta-item dietary"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Dietary:', 'quill'),
            esc_html(implode(', ', $meta['dietary']))
        );
    }

    $output .= '</div>';

    echo apply_filters('quill_recipe_meta_html', $output, $meta); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display recipe ingredients
 *
 * @param WP_Post|int|null $post Post object or ID.
 */
function quill_recipe_ingredients($post = null)
{
    $ingredients = quill_get_recipe_ingredients($post);
    if (empty($ingredients)) {
        return;
    }

    $output = '<div class="recipe-ingredients">';
    $output .= '<h3>' . esc_html__('Ingredients', 'quill') . '</h3>';
    $output .= '<ul class="ingredients-list">';

    foreach ($ingredients as $ingredient) {
        $output .= sprintf(
            '<li class="ingredient-item"><span class="amount">%1$s</span> <span class="unit">%2$s</span> <span class="name">%3$s</span></li>',
            esc_html($ingredient['amount']),
            esc_html($ingredient['unit']),
            esc_html($ingredient['name'])
        );
    }

    $output .= '</ul>';
    $output .= '</div>';

    echo apply_filters('quill_recipe_ingredients_html', $output, $ingredients); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display recipe instructions
 *
 * @param WP_Post|int|null $post Post object or ID.
 */
function quill_recipe_instructions($post = null)
{
    $instructions = quill_get_recipe_instructions($post);
    if (empty($instructions)) {
        return;
    }

    $output = '<div class="recipe-instructions">';
    $output .= '<h3>' . esc_html__('Instructions', 'quill') . '</h3>';
    $output .= '<ol class="instructions-list">';

    foreach ($instructions as $instruction) {
        $output .= sprintf(
            '<li class="instruction-item">%s</li>',
            wp_kses_post($instruction)
        );
    }

    $output .= '</ol>';
    $output .= '</div>';

    echo apply_filters('quill_recipe_instructions_html', $output, $instructions); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display recipe notes
 *
 * @param WP_Post|int|null $post Post object or ID.
 */
function quill_recipe_notes($post = null)
{
    $notes = quill_get_recipe_notes($post);
    if (empty($notes)) {
        return;
    }

    $output = '<div class="recipe-notes">';
    $output .= '<h3>' . esc_html__('Notes', 'quill') . '</h3>';
    $output .= wp_kses_post(wpautop($notes));
    $output .= '</div>';

    echo apply_filters('quill_recipe_notes_html', $output, $notes); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display recipe nutrition information
 *
 * @param WP_Post|int|null $post Post object or ID.
 */
function quill_recipe_nutrition($post = null)
{
    $nutrition = quill_get_recipe_nutrition($post);
    if (empty($nutrition)) {
        return;
    }

    $output = '<div class="recipe-nutrition">';
    $output .= '<h3>' . esc_html__('Nutrition Information', 'quill') . '</h3>';
    $output .= '<div class="nutrition-grid">';

    if (! empty($nutrition['calories'])) {
        $output .= sprintf(
            '<div class="nutrition-item calories"><span class="label">%1$s</span><span class="value">%2$s</span></div>',
            esc_html__('Calories:', 'quill'),
            esc_html($nutrition['calories'])
        );
    }

    if (! empty($nutrition['fat'])) {
        $output .= sprintf(
            '<div class="nutrition-item fat"><span class="label">%1$s</span><span class="value">%2$sg</span></div>',
            esc_html__('Fat:', 'quill'),
            esc_html($nutrition['fat'])
        );
    }

    if (! empty($nutrition['saturated_fat'])) {
        $output .= sprintf(
            '<div class="nutrition-item saturated-fat"><span class="label">%1$s</span><span class="value">%2$sg</span></div>',
            esc_html__('Saturated Fat:', 'quill'),
            esc_html($nutrition['saturated_fat'])
        );
    }

    if (! empty($nutrition['cholesterol'])) {
        $output .= sprintf(
            '<div class="nutrition-item cholesterol"><span class="label">%1$s</span><span class="value">%2$smg</span></div>',
            esc_html__('Cholesterol:', 'quill'),
            esc_html($nutrition['cholesterol'])
        );
    }

    if (! empty($nutrition['sodium'])) {
        $output .= sprintf(
            '<div class="nutrition-item sodium"><span class="label">%1$s</span><span class="value">%2$smg</span></div>',
            esc_html__('Sodium:', 'quill'),
            esc_html($nutrition['sodium'])
        );
    }

    if (! empty($nutrition['carbohydrates'])) {
        $output .= sprintf(
            '<div class="nutrition-item carbohydrates"><span class="label">%1$s</span><span class="value">%2$sg</span></div>',
            esc_html__('Carbohydrates:', 'quill'),
            esc_html($nutrition['carbohydrates'])
        );
    }

    if (! empty($nutrition['fiber'])) {
        $output .= sprintf(
            '<div class="nutrition-item fiber"><span class="label">%1$s</span><span class="value">%2$sg</span></div>',
            esc_html__('Fiber:', 'quill'),
            esc_html($nutrition['fiber'])
        );
    }

    if (! empty($nutrition['sugar'])) {
        $output .= sprintf(
            '<div class="nutrition-item sugar"><span class="label">%1$s</span><span class="value">%2$sg</span></div>',
            esc_html__('Sugar:', 'quill'),
            esc_html($nutrition['sugar'])
        );
    }

    if (! empty($nutrition['protein'])) {
        $output .= sprintf(
            '<div class="nutrition-item protein"><span class="label">%1$s</span><span class="value">%2$sg</span></div>',
            esc_html__('Protein:', 'quill'),
            esc_html($nutrition['protein'])
        );
    }

    $output .= '</div>';
    $output .= '</div>';

    echo apply_filters('quill_recipe_nutrition_html', $output, $nutrition); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Check if current request is AMP
 *
 * @return bool Whether current request is AMP.
 */
function quill_is_amp()
{
    return function_exists('amp_is_request') && amp_is_request();
}

/**
 * Get AdSense settings
 *
 * @return array AdSense settings.
 */
function quill_get_adsense_settings()
{
    $settings = get_option('quill_adsense_settings', array());
    $defaults = array(
        'publisher_id' => '',
        'slots'        => array(
            'header'        => '',
            'sidebar'       => '',
            'content'       => '',
            'recipe_top'    => '',
            'recipe_bottom' => '',
        ),
    );

    return wp_parse_args($settings, $defaults);
}

/**
 * Display AdSense ad
 *
 * @param string $slot Ad slot identifier.
 */
function quill_adsense_ad($slot)
{
    $settings = quill_get_adsense_settings();
    if (empty($settings['publisher_id']) || empty($settings['slots'][$slot])) {
        return;
    }

    if (quill_is_amp()) {
        printf(
            '<amp-ad width="100vw" height="320" type="adsense" data-ad-client="ca-%1$s" data-ad-slot="%2$s" data-auto-format="rspv" data-full-width><div overflow></div></amp-ad>',
            esc_attr($settings['publisher_id']),
            esc_attr($settings['slots'][$slot])
        );
    } else {
        printf(
            '<div class="adsense-ad %1$s"><ins class="adsbygoogle" style="display:block" data-ad-client="ca-%2$s" data-ad-slot="%3$s" data-ad-format="auto" data-full-width-responsive="true"></ins></div>',
            esc_attr($slot),
            esc_attr($settings['publisher_id']),
            esc_attr($settings['slots'][$slot])
        );
    }
}
