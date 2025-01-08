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
        // Get API key from wp-config.php
        $this->api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

        // Register REST API endpoint
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Show admin notice if API key is missing
        if (empty($this->api_key)) {
            add_action('admin_notices', array($this, 'api_key_notice'));
        }
    }

    /**
     * Display admin notice for missing API key
     */
    public function api_key_notice()
    {
?>
        <div class="notice notice-error">
            <p><?php _e('Quill Recipe Generator requires an OpenAI API key. Please add the following to your wp-config.php file:', 'quill'); ?></p>
            <pre>define('OPENAI_API_KEY', 'your-api-key-here');</pre>
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
        try {
            error_log('Calling OpenAI API with content length: ' . strlen($content));

            // Prepare request
            $args = array(
                'body' => wp_json_encode(array(
                    'model' => 'gpt-4',
                    'messages' => array(
                        array(
                            'role' => 'system',
                            'content' => 'You are a recipe writer. Generate clear, precise recipes with accurate measurements and timing. Format all times in ISO 8601 duration format (e.g. PT1H30M).'
                        ),
                        array(
                            'role' => 'user',
                            'content' => sprintf('Generate a recipe from this content: %s', $content)
                        )
                    ),
                    'functions' => array(
                        array(
                            'name' => 'generate_recipe',
                            'description' => 'Generate a recipe with all necessary details',
                            'parameters' => array(
                                'type' => 'object',
                                'properties' => array(
                                    'name' => array(
                                        'type' => 'string',
                                        'description' => 'Recipe name'
                                    ),
                                    'description' => array(
                                        'type' => 'string',
                                        'description' => 'Brief recipe description'
                                    ),
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
                                        'description' => 'Preparation time in ISO 8601 duration format (e.g. PT30M)'
                                    ),
                                    'cook_time' => array(
                                        'type' => 'string',
                                        'description' => 'Cooking time in ISO 8601 duration format (e.g. PT1H)'
                                    ),
                                    'total_time' => array(
                                        'type' => 'string',
                                        'description' => 'Total time in ISO 8601 duration format (e.g. PT1H30M)'
                                    ),
                                    'yield' => array(
                                        'type' => 'string',
                                        'description' => 'Number of servings (e.g. "4 servings")'
                                    ),
                                    'cuisine' => array(
                                        'type' => 'string',
                                        'description' => 'Type of cuisine (e.g. Italian, Mexican)'
                                    ),
                                    'categories' => array(
                                        'type' => 'array',
                                        'items' => array('type' => 'string'),
                                        'description' => 'Recipe categories'
                                    ),
                                    'keywords' => array(
                                        'type' => 'array',
                                        'items' => array('type' => 'string'),
                                        'description' => 'Keywords describing the recipe'
                                    ),
                                    'equipment' => array(
                                        'type' => 'array',
                                        'items' => array('type' => 'string'),
                                        'description' => 'Required cooking equipment'
                                    ),
                                    'notes' => array(
                                        'type' => 'array',
                                        'items' => array('type' => 'string'),
                                        'description' => 'Important recipe notes and tips'
                                    )
                                ),
                                'required' => ['name', 'description', 'ingredients', 'instructions', 'prep_time', 'cook_time', 'total_time', 'yield']
                            )
                        )
                    ),
                    'function_call' => array('name' => 'generate_recipe')
                )),
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 60,
                'httpversion' => '1.1',
                'sslverify' => true
            );

            // Make API request
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);

            // Handle response
            if (is_wp_error($response)) {
                error_log('OpenAI API Network Error: ' . $response->get_error_message());
                return $response;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            error_log('OpenAI API Response Code: ' . $response_code);
            error_log('OpenAI API Response Body: ' . $body);

            if ($response_code !== 200) {
                return new WP_Error(
                    'openai_error',
                    'OpenAI API returned error code: ' . $response_code,
                    array('status' => $response_code)
                );
            }

            $data = json_decode($body, true);
            if (empty($data['choices']) || !is_array($data['choices']) || empty($data['choices'][0]['message']['function_call'])) {
                error_log('Invalid OpenAI Response Structure: ' . wp_json_encode($data));
                return new WP_Error(
                    'openai_error',
                    'Invalid response structure from OpenAI',
                    array('status' => 500)
                );
            }

            $recipe = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Recipe Parse Error: ' . json_last_error_msg());
                return new WP_Error(
                    'parse_error',
                    'Failed to parse recipe data',
                    array('status' => 500)
                );
            }

            return $recipe;
        } catch (Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return new WP_Error(
                'openai_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
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
        // Get all available WordPress categories
        $wp_categories = get_categories(array('hide_empty' => false));
        $category_data = array_map(function ($cat) {
            return array(
                'id' => strval($cat->term_id), // Ensure IDs are strings
                'name' => $cat->name
            );
        }, $wp_categories);

        // Build system message with category data
        $system_message = "You are a culinary expert. Here are the ONLY valid recipe categories you can choose from (format: ID | Name):\n";
        foreach ($category_data as $cat) {
            $system_message .= sprintf("%s | %s\n", $cat['id'], $cat['name']);
        }
        $system_message .= "\nAnalyze the recipe and select ONLY from the category IDs listed above. DO NOT create new categories or use IDs not in this list.\n";
        $system_message .= "Return the category IDs as an array of strings.\n";
        $system_message .= "Example valid response format: ['534', '537', '538']\n";
        $system_message .= "Choose categories that best match the recipe's type, cuisine, and main ingredients.";

        $args = array(
            'body' => wp_json_encode(array(
                'model' => 'gpt-4',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => $system_message
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
                                    'type' => 'array',
                                    'items' => array(
                                        'type' => 'string'
                                    ),
                                    'description' => 'WordPress category IDs for the recipe'
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

        // Validate returned category IDs against our list
        if (!empty($recipe['category'])) {
            $valid_category_ids = array_map(function ($cat) {
                return $cat['id'];
            }, $category_data);

            $recipe['category'] = array_values(array_filter($recipe['category'], function ($id) use ($valid_category_ids) {
                return in_array($id, $valid_category_ids);
            }));
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

    public function register_rest_routes()
    {
        register_rest_route('quill/v1', '/generate-recipe', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_recipe_endpoint'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args' => array(
                'content' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer'
                )
            )
        ));
    }

    public function generate_recipe_endpoint($request)
    {
        try {
            // Check API key
            if (empty($this->api_key)) {
                return new WP_Error(
                    'openai_error',
                    'OpenAI API key is not configured',
                    array('status' => 500)
                );
            }

            // Get and validate parameters
            $content = $request->get_param('content');
            $post_id = $request->get_param('post_id');

            error_log('Starting recipe generation for post ' . $post_id);
            error_log('Content length: ' . strlen($content));

            // Call OpenAI API
            $response = $this->call_openai_api($content);

            // Handle errors
            if (is_wp_error($response)) {
                error_log('OpenAI API Error: ' . $response->get_error_message());
                return $response;
            }

            // Add post data if available
            if (!empty($post_id)) {
                $post = get_post($post_id);
                if ($post) {
                    $response['name'] = $post->post_title;
                    $response['description'] = get_the_excerpt($post);
                }
            }

            // Return success response
            return rest_ensure_response(array(
                'success' => true,
                'data' => $response
            ));
        } catch (Exception $e) {
            error_log('Recipe Generation Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return new WP_Error(
                'recipe_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }
}

// Initialize the OpenAI integration
new Quill_OpenAI();
