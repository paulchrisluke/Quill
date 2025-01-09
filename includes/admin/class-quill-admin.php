<?php

/**
 * Admin Module Class
 */
class Quill_Admin
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('category_add_form_fields', array($this, 'add_category_image_field'));
        add_action('category_edit_form_fields', array($this, 'edit_category_image_field'));
        add_action('created_category', array($this, 'save_category_image'));
        add_action('edited_category', array($this, 'save_category_image'));
    }

    /**
     * Add menu pages
     */
    public function add_menu_pages()
    {
        add_menu_page(
            __('Quill Settings', 'quill'),
            __('Quill', 'quill'),
            'manage_options',
            'quill',
            array($this, 'render_settings_page'),
            'dashicons-edit',
            30
        );

        add_submenu_page(
            'quill',
            __('AI Settings', 'quill'),
            __('AI Settings', 'quill'),
            'manage_options',
            'quill-ai',
            array($this, 'render_ai_settings_page')
        );

        add_submenu_page(
            'quill',
            __('Monetization', 'quill'),
            __('Monetization', 'quill'),
            'manage_options',
            'quill-monetization',
            array($this, 'render_monetization_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        // General settings
        register_setting('quill_settings', 'quill_recipe_layout');
        register_setting('quill_settings', 'quill_enable_amp');
        register_setting('quill_settings', 'quill_enable_schema');

        // AI settings
        register_setting('quill_ai_settings', 'openai_api_key');
        register_setting('quill_ai_settings', 'ai_content_generation');
        register_setting('quill_ai_settings', 'ai_image_generation');

        // Monetization settings
        register_setting('quill_monetization', 'adsense_client_id');
        register_setting('quill_monetization', 'adsense_auto_ads');
        register_setting('quill_monetization', 'affiliate_disclosure');
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook)
    {
        if (!in_array($hook, array('quill_page_quill-ai', 'quill_page_quill-monetization'))) {
            return;
        }

        wp_enqueue_style(
            'quill-admin',
            QUILL_ASSETS_URL . '/css/admin.css',
            array(),
            QUILL_VERSION
        );

        wp_enqueue_script(
            'quill-admin',
            QUILL_ASSETS_URL . '/js/admin.js',
            array('jquery'),
            QUILL_VERSION,
            true
        );

        wp_localize_script('quill-admin', 'quillAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('quill_admin_nonce')
        ));

        if (strpos($hook, 'category') !== false) {
            wp_enqueue_media();
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
?>
        <div class="wrap">
            <h1><?php _e('Quill Settings', 'quill'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('quill_settings');
                do_settings_sections('quill_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Recipe Layout', 'quill'); ?></th>
                        <td>
                            <select name="quill_recipe_layout">
                                <option value="classic" <?php selected(get_option('quill_recipe_layout'), 'classic'); ?>>
                                    <?php _e('Classic', 'quill'); ?>
                                </option>
                                <option value="modern" <?php selected(get_option('quill_recipe_layout'), 'modern'); ?>>
                                    <?php _e('Modern', 'quill'); ?>
                                </option>
                                <option value="minimal" <?php selected(get_option('quill_recipe_layout'), 'minimal'); ?>>
                                    <?php _e('Minimal', 'quill'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enable AMP', 'quill'); ?></th>
                        <td>
                            <input type="checkbox" name="quill_enable_amp" value="1" <?php checked(get_option('quill_enable_amp'), 1); ?>>
                            <p class="description"><?php _e('Enable Accelerated Mobile Pages support', 'quill'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enable Schema', 'quill'); ?></th>
                        <td>
                            <input type="checkbox" name="quill_enable_schema" value="1" <?php checked(get_option('quill_enable_schema'), 1); ?>>
                            <p class="description"><?php _e('Enable Schema.org markup for recipes', 'quill'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Render AI settings page
     */
    public function render_ai_settings_page()
    {
    ?>
        <div class="wrap">
            <h1><?php _e('AI Settings', 'quill'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('quill_ai_settings');
                do_settings_sections('quill_ai_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('OpenAI API Key', 'quill'); ?></th>
                        <td>
                            <input type="password" name="openai_api_key" value="<?php echo esc_attr(get_option('openai_api_key')); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter your OpenAI API key', 'quill'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Content Generation', 'quill'); ?></th>
                        <td>
                            <input type="checkbox" name="ai_content_generation" value="1" <?php checked(get_option('ai_content_generation'), 1); ?>>
                            <p class="description"><?php _e('Enable AI-powered content generation', 'quill'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Image Generation', 'quill'); ?></th>
                        <td>
                            <input type="checkbox" name="ai_image_generation" value="1" <?php checked(get_option('ai_image_generation'), 1); ?>>
                            <p class="description"><?php _e('Enable AI-powered image generation', 'quill'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Render monetization page
     */
    public function render_monetization_page()
    {
    ?>
        <div class="wrap">
            <h1><?php _e('Monetization Settings', 'quill'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('quill_monetization');
                do_settings_sections('quill_monetization');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('AdSense Client ID', 'quill'); ?></th>
                        <td>
                            <input type="text" name="adsense_client_id" value="<?php echo esc_attr(get_option('adsense_client_id')); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter your Google AdSense client ID', 'quill'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Auto Ads', 'quill'); ?></th>
                        <td>
                            <input type="checkbox" name="adsense_auto_ads" value="1" <?php checked(get_option('adsense_auto_ads'), 1); ?>>
                            <p class="description"><?php _e('Enable AdSense Auto ads', 'quill'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Affiliate Disclosure', 'quill'); ?></th>
                        <td>
                            <?php
                            wp_editor(
                                get_option('affiliate_disclosure'),
                                'affiliate_disclosure',
                                array(
                                    'textarea_name' => 'affiliate_disclosure',
                                    'textarea_rows' => 5,
                                    'media_buttons' => false
                                )
                            );
                            ?>
                            <p class="description"><?php _e('Enter your affiliate disclosure text', 'quill'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Add category image field
     */
    public function add_category_image_field()
    {
    ?>
        <div class="form-field term-image-wrap">
            <label for="category-image"><?php _e('Category Image', 'quill'); ?></label>
            <input type="hidden" id="category-image" name="category_image" class="custom-media-url" value="">
            <div id="category-image-wrapper"></div>
            <p>
                <input type="button" class="button button-secondary" value="<?php _e('Add Image', 'quill'); ?>" id="add-category-image">
                <input type="button" class="button button-secondary" value="<?php _e('Remove Image', 'quill'); ?>" id="remove-category-image" style="display:none;">
            </p>
        </div>
    <?php
    }

    /**
     * Edit category image field
     */
    public function edit_category_image_field($term)
    {
        $image_id = get_term_meta($term->term_id, 'category_image', true);
    ?>
        <tr class="form-field term-image-wrap">
            <th scope="row"><label for="category-image"><?php _e('Category Image', 'quill'); ?></label></th>
            <td>
                <input type="hidden" id="category-image" name="category_image" value="<?php echo $image_id; ?>">
                <div id="category-image-wrapper">
                    <?php if ($image_id) : ?>
                        <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                    <?php endif; ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary" value="<?php _e('Add Image', 'quill'); ?>" id="add-category-image">
                    <input type="button" class="button button-secondary" value="<?php _e('Remove Image', 'quill'); ?>" id="remove-category-image" <?php echo !$image_id ? 'style="display:none;"' : ''; ?>>
                </p>
            </td>
        </tr>
<?php
    }

    /**
     * Save category image
     */
    public function save_category_image($term_id)
    {
        if (isset($_POST['category_image'])) {
            update_term_meta($term_id, 'category_image', absint($_POST['category_image']));
        }
    }
}
