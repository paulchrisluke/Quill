<?php

/**
 * Nutrition Calculator for Recipe Analysis
 */
class Quill_Nutrition
{
    private $api_key;

    public function __construct()
    {
        $this->api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

        if (empty($this->api_key)) {
            return;
        }

        add_action('wp_ajax_quill_calculate_nutrition', array($this, 'calculate_nutrition'));
    }

    /**
     * Calculate nutrition information for a recipe
     */
    public function calculate_nutrition()
    {
        check_ajax_referer('quill_recipe_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $recipe = array(
            'ingredients' => sanitize_text_field($_POST['ingredients']),
            'instructions' => sanitize_text_field($_POST['instructions']),
            'prep_time' => sanitize_text_field($_POST['prep_time']),
            'cook_time' => sanitize_text_field($_POST['cook_time'])
        );

        $response = $this->analyze_recipe($recipe);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        wp_send_json_success($response);
    }

    /**
     * Analyze recipe using OpenAI
     */
    private function analyze_recipe($recipe)
    {
        $prompt = sprintf(
            "Analyze this recipe and provide detailed nutritional information:\n\nIngredients:\n%s\n\nInstructions:\n%s\n\nPrep Time: %s\nCook Time: %s",
            $recipe['ingredients'],
            $recipe['instructions'],
            $recipe['prep_time'],
            $recipe['cook_time']
        );

        $args = array(
            'body' => wp_json_encode(array(
                'model' => 'gpt-4',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a nutrition expert. Analyze recipes and provide accurate nutritional information.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'functions' => array(
                    array(
                        'name' => 'analyze_recipe',
                        'description' => 'Analyze recipe and provide nutritional information',
                        'parameters' => array(
                            'type' => 'object',
                            'properties' => array(
                                'yield' => array(
                                    'type' => 'string',
                                    'description' => 'Number of servings (e.g. "4 servings")'
                                ),
                                'cuisine' => array(
                                    'type' => 'string',
                                    'description' => 'Type of cuisine (e.g. Italian, Asian, American)'
                                ),
                                'category' => array(
                                    'type' => 'string',
                                    'description' => 'Recipe category (e.g. Main Course, Dessert, Beverage)'
                                ),
                                'keywords' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                    'description' => 'Key features and characteristics of the recipe'
                                ),
                                'notes' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                    'description' => 'Chef\'s tips and recipe notes'
                                ),
                                'equipment' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                    'description' => 'Required kitchen tools and equipment'
                                ),
                                'difficulty' => array(
                                    'type' => 'string',
                                    'enum' => array('Easy', 'Medium', 'Hard'),
                                    'description' => 'Recipe difficulty level'
                                ),
                                'nutrition' => array(
                                    'type' => 'object',
                                    'properties' => array(
                                        'servingSize' => array('type' => 'string'),
                                        'calories' => array('type' => 'string'),
                                        'fatContent' => array('type' => 'string'),
                                        'proteinContent' => array('type' => 'string'),
                                        'carbohydrateContent' => array('type' => 'string'),
                                        'fiberContent' => array('type' => 'string'),
                                        'sodiumContent' => array('type' => 'string')
                                    ),
                                    'required' => array('servingSize', 'calories', 'fatContent', 'proteinContent', 'carbohydrateContent', 'fiberContent', 'sodiumContent')
                                )
                            ),
                            'required' => ['yield', 'cuisine', 'category', 'keywords', 'notes', 'equipment', 'difficulty', 'nutrition']
                        )
                    )
                ),
                'function_call' => array('name' => 'analyze_recipe')
            )),
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['choices'][0]['message']['function_call'])) {
            return new WP_Error('openai_error', 'Invalid response from OpenAI');
        }

        $analysis = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('parse_error', 'Failed to parse nutrition data');
        }

        return $analysis;
    }
}

// Initialize the nutrition calculator
new Quill_Nutrition();
