<?php

/**
 * Schema.org Implementation
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

class Quill_Schema
{
    /**
     * Add schema markup to recipe content
     */
    public static function get_recipe_schema()
    {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => get_the_title(),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author()
            ),
            'datePublished' => get_the_date('c'),
            'description' => get_the_excerpt(),
            'image' => get_the_post_thumbnail_url(null, 'full'),
            'recipeCategory' => wp_get_post_terms(get_the_ID(), 'category', array('fields' => 'names')),
            'recipeCuisine' => wp_get_post_terms(get_the_ID(), 'cuisine', array('fields' => 'names')),
            'keywords' => wp_get_post_terms(get_the_ID(), 'post_tag', array('fields' => 'names')),
            'recipeYield' => get_post_meta(get_the_ID(), '_recipe_servings', true),
            'prepTime' => 'PT' . get_post_meta(get_the_ID(), '_recipe_prep_time', true) . 'M',
            'cookTime' => 'PT' . get_post_meta(get_the_ID(), '_recipe_cook_time', true) . 'M',
            'totalTime' => 'PT' . get_post_meta(get_the_ID(), '_recipe_total_time', true) . 'M',
            'recipeIngredient' => self::get_ingredients(),
            'recipeInstructions' => self::get_instructions(),
            'nutrition' => self::get_nutrition(),
        );

        return apply_filters('quill_recipe_schema', $schema);
    }

    /**
     * Get recipe ingredients as an array
     */
    private static function get_ingredients()
    {
        $ingredients_html = get_post_meta(get_the_ID(), '_recipe_ingredients', true);
        if (!$ingredients_html) {
            return array();
        }

        // Convert HTML list to array
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($ingredients_html, 'HTML-ENTITIES', 'UTF-8'));
        $items = $dom->getElementsByTagName('li');

        $ingredients = array();
        foreach ($items as $item) {
            $ingredients[] = trim($item->textContent);
        }

        return $ingredients;
    }

    /**
     * Get recipe instructions as an array of HowToStep
     */
    private static function get_instructions()
    {
        $instructions_html = get_post_meta(get_the_ID(), '_recipe_instructions', true);
        if (!$instructions_html) {
            return array();
        }

        // Convert HTML list to array of HowToStep objects
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($instructions_html, 'HTML-ENTITIES', 'UTF-8'));
        $items = $dom->getElementsByTagName('li');

        $instructions = array();
        $position = 1;
        foreach ($items as $item) {
            $instructions[] = array(
                '@type' => 'HowToStep',
                'position' => $position,
                'text' => trim($item->textContent)
            );
            $position++;
        }

        return $instructions;
    }

    /**
     * Get nutrition information
     */
    private static function get_nutrition()
    {
        $calories = get_post_meta(get_the_ID(), '_recipe_calories', true);
        if (!$calories) {
            return null;
        }

        return array(
            '@type' => 'NutritionInformation',
            'calories' => $calories . ' calories',
            'carbohydrateContent' => get_post_meta(get_the_ID(), '_recipe_carbs', true) . 'g',
            'proteinContent' => get_post_meta(get_the_ID(), '_recipe_protein', true) . 'g',
            'fatContent' => get_post_meta(get_the_ID(), '_recipe_fat', true) . 'g',
            'fiberContent' => get_post_meta(get_the_ID(), '_recipe_fiber', true) . 'g',
            'sugarContent' => get_post_meta(get_the_ID(), '_recipe_sugar', true) . 'g',
        );
    }

    /**
     * Output schema markup in JSON-LD format
     */
    public static function output_schema()
    {
        if (!is_singular('recipe')) {
            return;
        }

        $schema = self::get_recipe_schema();
        if (!$schema) {
            return;
        }

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }
}

// Add schema to wp_head
add_action('wp_head', array('Quill_Schema', 'output_schema'));
