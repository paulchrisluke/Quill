<?php

/**
 * OpenAI Integration for Recipe Generation
 */

class Quill_OpenAI
{
    /**
     * OpenAI API Key
     *
     * @var string
     */
    private $api_key;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

        if (empty($this->api_key)) {
            add_action('admin_notices', array($this, 'api_key_notice'));
            return;
        }

        add_action('wp_ajax_quill_generate_recipe', array($this, 'generate_recipe'));
        add_action('wp_ajax_quill_organize_recipe', array($this, 'organize_recipe'));
        add_action('wp_ajax_quill_calculate_nutrition', array($this, 'calculate_nutrition'));
    }

    /**
     * Display admin notice for missing API key
     */
    public function api_key_notice()
    {
?>
        <div class="notice notice-error">
            <p><?php _e('Quill Recipe Generator requires an OpenAI API key. Please add it to your wp-config.php file.', 'quill'); ?></p>
        </div>
<?php
    }

    /**
     * Generate recipe from post content using OpenAI
     */
    public function generate_recipe()
    {
        check_ajax_referer('quill_recipe_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $content = sanitize_text_field($_POST['content']);
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (empty($content)) {
            wp_send_json_error('No content provided');
        }

        $response = $this->call_openai_api($content);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        // Get post title and excerpt
        $post = get_post($post_id);
        $response['name'] = $post ? $post->post_title : '';
        $response['description'] = $post ? get_the_excerpt($post) : '';

        // Debug what we're sending back
        error_log('Sending response to frontend: ' . print_r($response, true));

        // Ensure we're sending a proper JSON response
        header('Content-Type: application/json');
        wp_send_json(array(
            'success' => true,
            'data' => $response
        ));
    }

    /**
     * Call OpenAI API to generate recipe details
     *
     * @param string $content Post content
     * @return array|WP_Error Recipe details or error
     */
    private function call_openai_api($content)
    {
        $args = array(
            'body' => wp_json_encode(array(
                'model' => 'gpt-4',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a recipe writer. Generate clear, precise recipes with accurate measurements and timing.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => sprintf('Generate a recipe from this content: %s', $content)
                    )
                ),
                'functions' => array(
                    array(
                        'name' => 'generate_recipe',
                        'description' => 'Generate a basic recipe with core details',
                        'parameters' => array(
                            'type' => 'object',
                            'properties' => array(
                                'ingredients' => array(
                                    'type' => 'string',
                                    'description' => 'List of ingredients with precise quantities, one per line'
                                ),
                                'instructions' => array(
                                    'type' => 'string',
                                    'description' => 'Step by step cooking instructions, one per line'
                                ),
                                'prep_time' => array(
                                    'type' => 'string',
                                    'description' => 'Preparation time in PT format (e.g. PT5M)'
                                ),
                                'cook_time' => array(
                                    'type' => 'string',
                                    'description' => 'Cooking time in PT format (e.g. PT30M)'
                                )
                            ),
                            'required' => ['ingredients', 'instructions', 'prep_time', 'cook_time']
                        )
                    )
                ),
                'function_call' => array('name' => 'generate_recipe')
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

        $recipe = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('parse_error', 'Failed to parse recipe data');
        }

        return $recipe;
    }

    /**
     * Organize recipe details and metadata
     */
    public function organize_recipe()
    {
        check_ajax_referer('quill_recipe_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $ingredients = sanitize_textarea_field($_POST['ingredients']);
        $instructions = sanitize_textarea_field($_POST['instructions']);
        $prep_time = sanitize_text_field($_POST['prep_time']);
        $cook_time = sanitize_text_field($_POST['cook_time']);
        $total_time = sanitize_text_field($_POST['total_time']);

        if (empty($ingredients) || empty($instructions)) {
            wp_send_json_error('Missing required recipe data');
        }

        $response = $this->call_openai_organize_api($ingredients, $instructions, $prep_time, $cook_time, $total_time);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        wp_send_json_success($response);
    }

    /**
     * Calculate nutrition facts for recipe
     */
    public function calculate_nutrition()
    {
        check_ajax_referer('quill_recipe_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $ingredients = sanitize_textarea_field($_POST['ingredients']);
        $servings = sanitize_text_field($_POST['servings']);

        if (empty($ingredients)) {
            wp_send_json_error('No ingredients provided');
        }

        $response = $this->call_openai_nutrition_api($ingredients, $servings);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        wp_send_json_success($response);
    }

    /**
     * Call OpenAI API to organize recipe details
     *
     * @param string $ingredients List of ingredients
     * @param string $instructions Cooking instructions
     * @param string $prep_time Preparation time
     * @param string $cook_time Cooking time
     * @param string $total_time Total time
     * @return array|WP_Error Recipe organization details or error
     */
    private function call_openai_organize_api($ingredients, $instructions, $prep_time, $cook_time, $total_time)
    {
        $args = array(
            'body' => wp_json_encode(array(
                'model' => 'gpt-4',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a recipe organization expert. Analyze recipes and provide structured metadata.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => sprintf(
                            "Organize this recipe:\n\nIngredients:\n%s\n\nInstructions:\n%s\n\nPrep Time: %s\nCook Time: %s\nTotal Time: %s",
                            $ingredients,
                            $instructions,
                            $prep_time,
                            $cook_time,
                            $total_time
                        )
                    )
                ),
                'functions' => array(
                    array(
                        'name' => 'organize_recipe',
                        'description' => 'Organize recipe metadata and details',
                        'parameters' => array(
                            'type' => 'object',
                            'properties' => array(
                                'yield' => array(
                                    'type' => 'string',
                                    'description' => 'Number of servings the recipe yields'
                                ),
                                'cuisine' => array(
                                    'type' => 'string',
                                    'description' => 'Type of cuisine (e.g., Italian, Mexican, etc.)'
                                ),
                                'category' => array(
                                    'type' => 'string',
                                    'description' => 'Recipe category (e.g., Main Course, Dessert, etc.)'
                                ),
                                'keywords' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                    'description' => 'Keywords describing the recipe'
                                ),
                                'notes' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                    'description' => 'Important recipe notes and tips'
                                ),
                                'equipment' => array(
                                    'type' => 'array',
                                    'items' => array('type' => 'string'),
                                    'description' => 'Required cooking equipment'
                                ),
                                'difficulty' => array(
                                    'type' => 'string',
                                    'description' => 'Recipe difficulty level (Easy, Medium, Hard)'
                                )
                            ),
                            'required' => ['yield', 'cuisine', 'category', 'keywords', 'notes', 'equipment', 'difficulty']
                        )
                    )
                ),
                'function_call' => array('name' => 'organize_recipe')
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

        $recipe = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('parse_error', 'Failed to parse recipe data');
        }

        return $recipe;
    }

    /**
     * Call OpenAI API to calculate nutrition facts
     *
     * @param string $ingredients List of ingredients
     * @param string $servings Number of servings
     * @return array|WP_Error Nutrition facts or error
     */
    private function call_openai_nutrition_api($ingredients, $servings)
    {
        $args = array(
            'body' => wp_json_encode(array(
                'model' => 'gpt-4',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a nutrition expert. Calculate detailed nutrition facts for recipes.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => sprintf(
                            "Calculate nutrition facts for these ingredients (serves %s):\n\n%s",
                            $servings,
                            $ingredients
                        )
                    )
                ),
                'functions' => array(
                    array(
                        'name' => 'calculate_nutrition',
                        'description' => 'Calculate nutrition facts per serving',
                        'parameters' => array(
                            'type' => 'object',
                            'properties' => array(
                                'servingSize' => array(
                                    'type' => 'string',
                                    'description' => 'Size of one serving (e.g., "1 cup", "250g")'
                                ),
                                'servings' => array(
                                    'type' => 'string',
                                    'description' => 'Number of servings'
                                ),
                                'calories' => array(
                                    'type' => 'string',
                                    'description' => 'Calories per serving'
                                ),
                                'fatContent' => array(
                                    'type' => 'string',
                                    'description' => 'Total fat in grams'
                                ),
                                'saturatedFatContent' => array(
                                    'type' => 'string',
                                    'description' => 'Saturated fat in grams'
                                ),
                                'cholesterolContent' => array(
                                    'type' => 'string',
                                    'description' => 'Cholesterol in milligrams'
                                ),
                                'sodiumContent' => array(
                                    'type' => 'string',
                                    'description' => 'Sodium in milligrams'
                                ),
                                'carbohydrateContent' => array(
                                    'type' => 'string',
                                    'description' => 'Total carbohydrates in grams'
                                ),
                                'fiberContent' => array(
                                    'type' => 'string',
                                    'description' => 'Dietary fiber in grams'
                                ),
                                'sugarContent' => array(
                                    'type' => 'string',
                                    'description' => 'Sugars in grams'
                                ),
                                'proteinContent' => array(
                                    'type' => 'string',
                                    'description' => 'Protein in grams'
                                ),
                                'vitaminC' => array(
                                    'type' => 'string',
                                    'description' => 'Vitamin C as % Daily Value'
                                ),
                                'calciumContent' => array(
                                    'type' => 'string',
                                    'description' => 'Calcium as % Daily Value'
                                ),
                                'ironContent' => array(
                                    'type' => 'string',
                                    'description' => 'Iron as % Daily Value'
                                ),
                                'vitaminD' => array(
                                    'type' => 'string',
                                    'description' => 'Vitamin D as % Daily Value'
                                ),
                                'potassiumContent' => array(
                                    'type' => 'string',
                                    'description' => 'Potassium in milligrams'
                                )
                            ),
                            'required' => [
                                'servingSize',
                                'servings',
                                'calories',
                                'fatContent',
                                'saturatedFatContent',
                                'cholesterolContent',
                                'sodiumContent',
                                'carbohydrateContent',
                                'fiberContent',
                                'sugarContent',
                                'proteinContent',
                                'vitaminC',
                                'calciumContent',
                                'ironContent',
                                'vitaminD',
                                'potassiumContent'
                            ]
                        )
                    )
                ),
                'function_call' => array('name' => 'calculate_nutrition')
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

        $nutrition = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('parse_error', 'Failed to parse nutrition data');
        }

        return $nutrition;
    }
}

// Initialize the OpenAI integration
new Quill_OpenAI();
