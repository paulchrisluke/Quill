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

        if (empty($content)) {
            wp_send_json_error('No content provided');
        }

        $response = $this->call_openai_api($content);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        wp_send_json_success($response);
    }

    /**
     * Call OpenAI API to generate recipe details
     *
     * @param string $content Post content
     * @return array|WP_Error Recipe details or error
     */
    private function call_openai_api($content)
    {
        $prompt = sprintf(
            "Generate a recipe based on the following content. Format the response as JSON with the following structure:\n" .
                "{\n" .
                "  \"ingredients\": [array of ingredients, one per line],\n" .
                "  \"instructions\": [array of steps, one per line],\n" .
                "  \"prep_time\": \"preparation time in ISO 8601 duration format\",\n" .
                "  \"cook_time\": \"cooking time in ISO 8601 duration format\"\n" .
                "}\n\n" .
                "Content:\n%s",
            $content
        );

        $args = array(
            'body' => wp_json_encode(array(
                'model' => 'gpt-4',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a professional chef and recipe writer. Create detailed, accurate recipes with precise measurements and clear instructions.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'temperature' => 0.7,
                'max_tokens' => 1000
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

        if (empty($data['choices'][0]['message']['content'])) {
            return new WP_Error('openai_error', 'Invalid response from OpenAI');
        }

        $recipe = json_decode($data['choices'][0]['message']['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('parse_error', 'Failed to parse recipe data');
        }

        return array(
            'ingredients' => implode("\n", $recipe['ingredients']),
            'instructions' => implode("\n", $recipe['instructions']),
            'prep_time' => $recipe['prep_time'],
            'cook_time' => $recipe['cook_time']
        );
    }
}

// Initialize the OpenAI integration
new Quill_OpenAI();
