<?php

/**
 * Quill Theme Functions
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress Core
require_once(ABSPATH . 'wp-includes/plugin.php');
require_once(ABSPATH . 'wp-includes/theme.php');
require_once(ABSPATH . 'wp-includes/general-template.php');
require_once(ABSPATH . 'wp-includes/category.php');
require_once(ABSPATH . 'wp-includes/category-template.php');
require_once(ABSPATH . 'wp-includes/post.php');
require_once(ABSPATH . 'wp-includes/link-template.php');
require_once(ABSPATH . 'wp-includes/formatting.php');
require_once(ABSPATH . 'wp-includes/capabilities.php');

// Define theme constants
define('QUILL_VERSION', '1.0.0');

// Load OpenAI integration
require_once get_template_directory() . '/includes/class-quill-openai.php';

/**
 * Theme Setup
 */
function quill_setup()
{
    // Add post thumbnail support
    add_theme_support('post-thumbnails');

    // Add title tag support
    add_theme_support('title-tag');

    // Add HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Register nav menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'quill'),
        'footer' => __('Footer Menu', 'quill'),
    ));
}
add_action('after_setup_theme', 'quill_setup');

/**
 * Register sidebar
 */
function quill_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Main Sidebar', 'quill'),
        'id'            => 'sidebar1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'quill'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'quill_widgets_init');

/**
 * Register and load sidebar scripts
 */
function quill_enqueue_sidebar_scripts()
{
    if (!is_admin()) {
        wp_enqueue_script('amp-sidebar', 'https://cdn.ampproject.org/v0/amp-sidebar-0.1.js', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'quill_enqueue_sidebar_scripts');

/**
 * Ensure all images and iframes are AMP-compliant
 */
function quill_make_content_amp_compliant($content)
{
    // Convert img tags to amp-img
    $content = preg_replace(
        '/<img([^>]+?)>/i',
        '<amp-img$1 layout="responsive"></amp-img>',
        $content
    );

    // Convert iframes to amp-iframe
    $content = preg_replace(
        '/<iframe([^>]+?)>/i',
        '<amp-iframe$1 layout="responsive" sandbox="allow-scripts allow-same-origin"></amp-iframe>',
        $content
    );

    return $content;
}
add_filter('the_content', 'quill_make_content_amp_compliant', 99);
add_filter('post_thumbnail_html', 'quill_make_content_amp_compliant', 99);

/**
 * Register Recipe Custom Post Meta
 */
function quill_register_recipe_meta()
{
    register_post_meta('post', '_recipe_ingredients', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));

    register_post_meta('post', '_recipe_instructions', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));

    register_post_meta('post', '_recipe_cook_time', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));

    register_post_meta('post', '_recipe_prep_time', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
    ));
}
add_action('init', 'quill_register_recipe_meta');

/**
 * Add Recipe Generator Block
 */
function quill_enqueue_block_editor_assets()
{
    wp_enqueue_script(
        'quill-recipe-block',
        get_template_directory_uri() . '/js/recipe-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-api-fetch'),
        QUILL_VERSION
    );

    wp_localize_script('quill-recipe-block', 'quillData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('quill_recipe_nonce'),
    ));
}
add_action('enqueue_block_editor_assets', 'quill_enqueue_block_editor_assets');

/**
 * Handle Recipe Generation AJAX Request
 */
function quill_generate_recipe()
{
    check_ajax_referer('quill_recipe_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }

    $post_content = sanitize_text_field($_POST['content']);

    if (empty($post_content)) {
        wp_send_json_error('No content provided');
    }

    $response = quill_call_openai_api($post_content);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_quill_generate_recipe', 'quill_generate_recipe');

/**
 * Call OpenAI API to generate recipe details
 */
function quill_call_openai_api($content)
{
    error_log('Starting OpenAI API call with content length: ' . strlen($content));

    if (!defined('OPENAI_API_KEY')) {
        error_log('OPENAI_API_KEY constant is not defined');
        return new WP_Error('openai_error', 'OpenAI API key not found - constant not defined');
    }

    $api_key = OPENAI_API_KEY;
    if (empty($api_key)) {
        error_log('OPENAI_API_KEY is defined but empty');
        return new WP_Error('openai_error', 'OpenAI API key is empty');
    }

    error_log('API Key found, first 10 chars: ' . substr($api_key, 0, 10));

    $prompt = sprintf(
        "Generate a recipe based on the following content. Format the response as JSON with the following structure:\n" .
            "{\n" .
            "  \"ingredients\": [array of ingredients, one per line],\n" .
            "  \"instructions\": [array of steps, one per line],\n" .
            "  \"prep_time\": \"preparation time in ISO 8601 duration format (e.g., PT1H30M for 1 hour 30 minutes)\",\n" .
            "  \"cook_time\": \"cooking time in ISO 8601 duration format (e.g., PT2H for 2 hours)\"\n" .
            "}\n\n" .
            "Important formatting rules:\n" .
            "1. Format ALL time durations in the instructions using ISO 8601 format (e.g., 'Soak for PT30M' not 'Soak for 30 minutes')\n" .
            "2. Always start time durations with 'PT' followed by hours (H) and/or minutes (M)\n" .
            "3. Be precise with timing in the instructions\n" .
            "4. Break down complex steps into separate instructions\n\n" .
            "Content:\n%s",
        $content
    );

    error_log('Generated prompt: ' . $prompt);

    // Set longer timeout and additional options
    $args = array(
        'timeout' => 60, // Increase timeout to 60 seconds
        'body' => wp_json_encode(array(
            'model' => 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a professional chef and recipe writer. Create detailed, accurate recipes with precise measurements and clear instructions. Always format durations in ISO 8601 format (e.g., PT1H30M for 1 hour 30 minutes).'
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
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ),
        'sslverify' => true,
        'redirection' => 5,
        'httpversion' => '1.1',
        'blocking' => true,
        'cookies' => array()
    );

    error_log('Making request to OpenAI API with timeout: ' . $args['timeout']);
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('OpenAI API request failed: ' . $error_message);
        return new WP_Error('openai_error', 'API request failed: ' . $error_message);
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $error_message = wp_remote_retrieve_response_message($response);
        error_log('OpenAI API returned non-200 status code: ' . $response_code . ' - ' . $error_message);
        return new WP_Error('openai_error', 'API returned error: ' . $error_message);
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        error_log('OpenAI API returned empty response body');
        return new WP_Error('openai_error', 'Empty response from API');
    }

    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Failed to parse API response: ' . json_last_error_msg());
        return new WP_Error('parse_error', 'Failed to parse API response');
    }

    error_log('OpenAI API response: ' . print_r($data, true));

    if (empty($data['choices'][0]['message']['content'])) {
        error_log('Invalid response from OpenAI: empty content');
        return new WP_Error('openai_error', 'Invalid response from OpenAI');
    }

    $recipe = json_decode($data['choices'][0]['message']['content'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Failed to parse recipe JSON: ' . json_last_error_msg());
        return new WP_Error('parse_error', 'Failed to parse recipe data');
    }

    // Ensure times are in proper ISO 8601 format
    if (!empty($recipe['prep_time']) && strpos($recipe['prep_time'], 'PT') !== 0) {
        $recipe['prep_time'] = 'PT' . $recipe['prep_time'];
    }
    if (!empty($recipe['cook_time']) && strpos($recipe['cook_time'], 'PT') !== 0) {
        $recipe['cook_time'] = 'PT' . $recipe['cook_time'];
    }

    error_log('Successfully generated recipe: ' . print_r($recipe, true));

    return array(
        'ingredients' => implode("\n", $recipe['ingredients']),
        'instructions' => implode("\n", $recipe['instructions']),
        'prep_time' => $recipe['prep_time'],
        'cook_time' => $recipe['cook_time']
    );
}

add_action('admin_notices', function () {
    if (defined('OPENAI_API_KEY')) {
        error_log('OpenAI API Key is defined: ' . substr(OPENAI_API_KEY, 0, 10) . '...');
    } else {
        error_log('OpenAI API Key is NOT defined in wp-config.php');
    }
});

/**
 * Add AMP attributes to html tag
 */
function quill_add_amp_html_attr($attr)
{
    return $attr . ' amp';
}
add_filter('language_attributes', 'quill_add_amp_html_attr');

/**
 * Add AMP viewport meta
 */
function quill_add_amp_viewport()
{
    echo '<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">';
}
add_action('wp_head', 'quill_add_amp_viewport', 1);

/**
 * Enqueue AMP scripts in head
 */
function quill_amp_scripts()
{
?>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
<?php
}
add_action('wp_head', 'quill_amp_scripts');

/**
 * Add AMP boilerplate styles to head
 */
function quill_amp_boilerplate_styles()
{
?>
    <style amp-boilerplate>
        body {
            -webkit-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            -moz-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            -ms-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            animation: -amp-start 8s steps(1, end) 0s 1 normal both
        }

        @-webkit-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-moz-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-ms-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-o-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }
    </style>
    <noscript>
        <style amp-boilerplate>
            body {
                -webkit-animation: none;
                -moz-animation: none;
                -ms-animation: none;
                animation: none
            }
        </style>
    </noscript>
    <?php
}
add_action('wp_head', 'quill_amp_boilerplate_styles', 0);

/**
 * Add image upload field to category admin page
 */
function quill_add_category_image_field($term)
{
    $image_id = get_term_meta($term->term_id, 'category_image_id', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';

    // Different markup for add vs edit screen
    if (is_object($term)) {
        // Edit category page 
    ?>
        <tr class="form-field term-image-wrap">
            <th scope="row"><label for="category-image-id"><?php _e('Category Image', 'quill'); ?></label></th>
            <td>
                <input type="hidden" id="category-image-id" name="category_image_id" value="<?php echo $image_id; ?>">
                <div id="category-image-wrapper">
                    <?php if ($image_url) : ?>
                        <img src="<?php echo esc_url($image_url); ?>" width="100" height="100" style="object-fit: cover; border-radius: 50%; margin-bottom: 10px;">
                    <?php endif; ?>
                </div>
                <input type="button" class="button button-secondary" id="upload-category-image" value="<?php _e('Upload Image', 'quill'); ?>">
                <input type="button" class="button button-secondary" id="remove-category-image" value="<?php _e('Remove Image', 'quill'); ?>" <?php echo !$image_id ? 'style="display:none;"' : ''; ?>>
                <p class="description"><?php _e('Upload or select an image for the category. This will be displayed in the sidebar menu.', 'quill'); ?></p>
            </td>
        </tr>
    <?php } else {
        // Add category page 
    ?>
        <div class="form-field term-image-wrap">
            <label for="category-image-id"><?php _e('Category Image', 'quill'); ?></label>
            <input type="hidden" id="category-image-id" name="category_image_id" value="">
            <div id="category-image-wrapper"></div>
            <input type="button" class="button button-secondary" id="upload-category-image" value="<?php _e('Upload Image', 'quill'); ?>">
            <input type="button" class="button button-secondary" id="remove-category-image" value="<?php _e('Remove Image', 'quill'); ?>" style="display:none;">
            <p class="description"><?php _e('Upload or select an image for the category. This will be displayed in the sidebar menu.', 'quill'); ?></p>
        </div>
    <?php }

    // Add inline styles for admin 
    ?>
    <style>
        .term-image-wrap #category-image-wrapper {
            margin: 10px 0;
            min-height: 0;
        }

        .term-image-wrap .button {
            margin-right: 10px;
        }

        .term-image-wrap img {
            max-width: 100px;
            height: auto;
            display: block;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            var frame;
            $('#upload-category-image').on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '<?php _e("Select or Upload Category Image", "quill"); ?>',
                    button: {
                        text: '<?php _e("Use this image", "quill"); ?>'
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#category-image-id').val(attachment.id);
                    $('#category-image-wrapper').html('<img src="' + attachment.url + '" width="100" height="100" style="object-fit: cover; border-radius: 50%; margin-bottom: 10px;">');
                    $('#remove-category-image').show();
                });

                frame.open();
            });

            $('#remove-category-image').on('click', function(e) {
                e.preventDefault();
                $('#category-image-id').val('');
                $('#category-image-wrapper').html('');
                $(this).hide();
            });
        });
    </script>
<?php
}
add_action('category_add_form_fields', 'quill_add_category_image_field');
add_action('category_edit_form_fields', 'quill_add_category_image_field');

/**
 * Save category image
 */
function quill_save_category_image($term_id)
{
    if (isset($_POST['category_image_id'])) {
        update_term_meta($term_id, 'category_image_id', absint($_POST['category_image_id']));
    }
}
add_action('created_category', 'quill_save_category_image');
add_action('edited_category', 'quill_save_category_image');

/**
 * Get category image URL
 */
function quill_get_category_image_url($term_id, $size = 'thumbnail')
{
    $image_id = get_term_meta($term_id, 'category_image_id', true);
    return $image_id ? wp_get_attachment_image_url($image_id, $size) : '';
}

/**
 * Enqueue admin scripts
 */
function quill_admin_scripts()
{
    if (is_admin()) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'quill_admin_scripts');

/**
 * Load and Initialize Quill Classes
 */
function quill_load_classes()
{
    // Load required class files
    require_once get_template_directory() . '/includes/class-quill-openai.php';
    require_once get_template_directory() . '/includes/class-quill-recipe.php';

    // Initialize classes
    global $quill_openai, $quill_recipe;
    $quill_openai = new Quill_OpenAI();
    $quill_recipe = new Quill_Recipe();
}
add_action('init', 'quill_load_classes');

/**
 * Add additional schema markup for recipes
 */
function quill_add_recipe_schema()
{
    if (!is_singular('post')) return;

    global $post;

    // Get site info
    $site_url = get_site_url();
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    $logo = get_theme_mod('custom_logo');
    $logo_url = $logo ? wp_get_attachment_image_url($logo, 'full') : '';

    // Get post info
    $post_url = get_permalink();
    $post_title = get_the_title();
    $post_excerpt = get_the_excerpt();
    $post_date = get_the_date('c');
    $modified_date = get_the_modified_date('c');
    $word_count = str_word_count(strip_tags($post->post_content));
    $comment_count = get_comments_number();

    // Get author info
    $author_id = get_the_author_meta('ID');
    $author_name = get_the_author();
    $author_url = get_author_posts_url($author_id);

    // Get featured image info
    $thumbnail_id = get_post_thumbnail_id();
    $thumbnail_url = get_the_post_thumbnail_url(null, 'full');
    $thumbnail_width = 1200; // Default width
    $thumbnail_height = 2133; // Default height

    if ($thumbnail_id) {
        $thumbnail_meta = wp_get_attachment_metadata($thumbnail_id);
        if ($thumbnail_meta) {
            $thumbnail_width = $thumbnail_meta['width'];
            $thumbnail_height = $thumbnail_meta['height'];
        }
    }

    // Get breadcrumb data
    $categories = get_the_category();
    $breadcrumbs = array();
    $breadcrumbs[] = array(
        'position' => 1,
        'name' => 'Home',
        'item' => $site_url
    );

    if (!empty($categories)) {
        $main_category = $categories[0];
        if ($main_category->parent != 0) {
            $parent = get_term($main_category->parent, 'category');
            $breadcrumbs[] = array(
                'position' => 2,
                'name' => $parent->name,
                'item' => get_term_link($parent)
            );
            $breadcrumbs[] = array(
                'position' => 3,
                'name' => $main_category->name,
                'item' => get_term_link($main_category)
            );
            $breadcrumbs[] = array(
                'position' => 4,
                'name' => $post_title
            );
        } else {
            $breadcrumbs[] = array(
                'position' => 2,
                'name' => $main_category->name,
                'item' => get_term_link($main_category)
            );
            $breadcrumbs[] = array(
                'position' => 3,
                'name' => $post_title
            );
        }
    }

    // Get social media links
    $social_links = array(
        'youtube' => 'https://www.youtube.com/@tiffyycooks',
        'tiktok' => 'https://www.tiktok.com/@tiffycooks',
        'instagram' => 'https://www.instagram.com/tiffy.cooks/'
    );

    // Build schema array
    $schema = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            array(
                '@type' => 'Article',
                '@id' => $post_url . '#article',
                'headline' => $post_title,
                'description' => $post_excerpt,
                'datePublished' => $post_date,
                'dateModified' => $modified_date,
                'wordCount' => $word_count,
                'commentCount' => $comment_count,
                'articleSection' => wp_list_pluck($categories, 'name'),
                'inLanguage' => get_locale(),
                'author' => array(
                    '@type' => 'Person',
                    '@id' => $site_url . '/#/schema/person/' . md5($author_name),
                    'name' => $author_name,
                    'url' => $author_url
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    '@id' => $site_url . '/#organization',
                    'name' => $site_name,
                    'url' => $site_url,
                    'sameAs' => array_values($social_links),
                    'logo' => array(
                        '@type' => 'ImageObject',
                        '@id' => $site_url . '/#/schema/logo/image/',
                        'url' => $logo_url,
                        'contentUrl' => $logo_url,
                        'width' => 1000,
                        'height' => 300,
                        'caption' => $site_name
                    )
                )
            ),
            array(
                '@type' => 'WebPage',
                '@id' => $post_url,
                'url' => $post_url,
                'name' => $post_title . ' - ' . $site_name,
                'description' => $post_excerpt,
                'datePublished' => $post_date,
                'dateModified' => $modified_date,
                'inLanguage' => get_locale(),
                'isPartOf' => array(
                    '@type' => 'WebSite',
                    '@id' => $site_url . '/#website',
                    'url' => $site_url,
                    'name' => $site_name,
                    'description' => $site_description,
                    'publisher' => array('@id' => $site_url . '/#organization')
                ),
                'primaryImageOfPage' => array(
                    '@type' => 'ImageObject',
                    '@id' => $post_url . '#primaryimage',
                    'url' => $thumbnail_url,
                    'contentUrl' => $thumbnail_url,
                    'width' => $thumbnail_width,
                    'height' => $thumbnail_height
                ),
                'breadcrumb' => array(
                    '@type' => 'BreadcrumbList',
                    '@id' => $post_url . '#breadcrumb',
                    'itemListElement' => array_map(function ($crumb) {
                        return array(
                            '@type' => 'ListItem',
                            'position' => $crumb['position'],
                            'name' => $crumb['name'],
                            'item' => isset($crumb['item']) ? $crumb['item'] : null
                        );
                    }, $breadcrumbs)
                )
            )
        )
    );

    // Output schema
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}
add_action('wp_footer', 'quill_add_recipe_schema');
