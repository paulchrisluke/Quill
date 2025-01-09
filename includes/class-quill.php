<?php

/**
 * Main theme class
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once ABSPATH . 'wp-includes/functions.php';

/**
 * Main theme class
 */
class Quill
{
    /**
     * Instance of this class
     *
     * @var Quill
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return Quill
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->load_files();
        add_action('after_setup_theme', array($this, 'init'));

        // Remove features that conflict with AMP
        add_action('init', array($this, 'remove_conflicting_features'));
    }

    /**
     * Remove features that conflict with AMP
     */
    private function remove_conflicting_features()
    {
        // Remove jQuery and other default scripts
        wp_dequeue_script('jquery');
        wp_deregister_script('jquery');
        wp_dequeue_script('wp-embed');
        wp_deregister_script('wp-embed');
    }

    /**
     * Initialize theme features
     */
    public function init()
    {
        // Register recipe post type
        add_action('init', array($this, 'register_recipe_post_type'));

        // Register recipe taxonomies
        add_action('init', array($this, 'register_recipe_taxonomies'));

        // Add recipe meta boxes
        add_action('load-post.php', array($this, 'init_recipe_meta'));
        add_action('load-post-new.php', array($this, 'init_recipe_meta'));

        // Add schema support
        add_action('wp_head', array($this, 'add_schema_support'));

        // Add theme supports
        $this->add_theme_supports();
    }

    /**
     * Load required files
     */
    private function load_files()
    {
        // Load admin files if in admin
        if (is_admin()) {
            require_once QUILL_DIR . '/includes/admin/class-quill-recipe-meta.php';
        }
    }

    /**
     * Register recipe post type
     */
    public function register_recipe_post_type()
    {
        $labels = array(
            'name'                  => _x('Recipes', 'Post type general name', 'quill'),
            'singular_name'         => _x('Recipe', 'Post type singular name', 'quill'),
            'menu_name'            => _x('Recipes', 'Admin Menu text', 'quill'),
            'name_admin_bar'        => _x('Recipe', 'Add New on Toolbar', 'quill'),
            'add_new'              => __('Add New', 'quill'),
            'add_new_item'         => __('Add New Recipe', 'quill'),
            'new_item'             => __('New Recipe', 'quill'),
            'edit_item'            => __('Edit Recipe', 'quill'),
            'view_item'            => __('View Recipe', 'quill'),
            'all_items'            => __('All Recipes', 'quill'),
            'search_items'         => __('Search Recipes', 'quill'),
            'parent_item_colon'    => __('Parent Recipes:', 'quill'),
            'not_found'            => __('No recipes found.', 'quill'),
            'not_found_in_trash'   => __('No recipes found in Trash.', 'quill'),
            'featured_image'        => _x('Recipe Cover Image', 'Overrides the "Featured Image" phrase', 'quill'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the "Set featured image" phrase', 'quill'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase', 'quill'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the "Use as featured image" phrase', 'quill'),
            'archives'              => _x('Recipe archives', 'The post type archive label used in nav menus', 'quill'),
            'insert_into_item'      => _x('Insert into recipe', 'Overrides the "Insert into post" phrase', 'quill'),
            'uploaded_to_this_item' => _x('Uploaded to this recipe', 'Overrides the "Uploaded to this post" phrase', 'quill'),
            'filter_items_list'     => _x('Filter recipes list', 'Screen reader text for the filter links', 'quill'),
            'items_list_navigation' => _x('Recipes list navigation', 'Screen reader text for the pagination', 'quill'),
            'items_list'            => _x('Recipes list', 'Screen reader text for the items list', 'quill'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'recipe'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-food',
            'supports'           => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'comments',
                'revisions',
            ),
            'show_in_rest'       => true,
            'template'           => array(
                array('core/paragraph', array(
                    'placeholder' => __('Add recipe description...', 'quill'),
                )),
            ),
        );

        register_post_type('recipe', $args);
    }

    /**
     * Register recipe taxonomies
     */
    public function register_recipe_taxonomies()
    {
        // Register Course taxonomy
        register_taxonomy(
            'course',
            'recipe',
            array(
                'labels'            => array(
                    'name'              => _x('Courses', 'taxonomy general name', 'quill'),
                    'singular_name'     => _x('Course', 'taxonomy singular name', 'quill'),
                    'search_items'      => __('Search Courses', 'quill'),
                    'all_items'         => __('All Courses', 'quill'),
                    'parent_item'       => __('Parent Course', 'quill'),
                    'parent_item_colon' => __('Parent Course:', 'quill'),
                    'edit_item'         => __('Edit Course', 'quill'),
                    'update_item'       => __('Update Course', 'quill'),
                    'add_new_item'      => __('Add New Course', 'quill'),
                    'new_item_name'     => __('New Course Name', 'quill'),
                    'menu_name'         => __('Courses', 'quill'),
                ),
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => 'course'),
                'show_in_rest'      => true,
            )
        );

        // Register Cuisine taxonomy
        register_taxonomy(
            'cuisine',
            'recipe',
            array(
                'labels'            => array(
                    'name'              => _x('Cuisines', 'taxonomy general name', 'quill'),
                    'singular_name'     => _x('Cuisine', 'taxonomy singular name', 'quill'),
                    'search_items'      => __('Search Cuisines', 'quill'),
                    'all_items'         => __('All Cuisines', 'quill'),
                    'parent_item'       => __('Parent Cuisine', 'quill'),
                    'parent_item_colon' => __('Parent Cuisine:', 'quill'),
                    'edit_item'         => __('Edit Cuisine', 'quill'),
                    'update_item'       => __('Update Cuisine', 'quill'),
                    'add_new_item'      => __('Add New Cuisine', 'quill'),
                    'new_item_name'     => __('New Cuisine Name', 'quill'),
                    'menu_name'         => __('Cuisines', 'quill'),
                ),
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => 'cuisine'),
                'show_in_rest'      => true,
            )
        );

        // Register Dietary taxonomy
        register_taxonomy(
            'dietary',
            'recipe',
            array(
                'labels'            => array(
                    'name'              => _x('Dietary', 'taxonomy general name', 'quill'),
                    'singular_name'     => _x('Dietary', 'taxonomy singular name', 'quill'),
                    'search_items'      => __('Search Dietary', 'quill'),
                    'all_items'         => __('All Dietary', 'quill'),
                    'parent_item'       => __('Parent Dietary', 'quill'),
                    'parent_item_colon' => __('Parent Dietary:', 'quill'),
                    'edit_item'         => __('Edit Dietary', 'quill'),
                    'update_item'       => __('Update Dietary', 'quill'),
                    'add_new_item'      => __('Add New Dietary', 'quill'),
                    'new_item_name'     => __('New Dietary Name', 'quill'),
                    'menu_name'         => __('Dietary', 'quill'),
                ),
                'hierarchical'      => false,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => 'dietary'),
                'show_in_rest'      => true,
            )
        );
    }

    /**
     * Initialize recipe meta boxes
     */
    public function init_recipe_meta()
    {
        $recipe_meta = new Quill_Recipe_Meta();
        $recipe_meta->init();
    }

    /**
     * Add schema support
     */
    public function add_schema_support()
    {
        if (is_singular('recipe')) {
            get_template_part('template-parts/recipe', 'schema');
        }
    }

    /**
     * Add theme supports
     */
    private function add_theme_supports()
    {
        // Add default posts and comments RSS feed links to head
        add_theme_support('automatic-feed-links');

        // Let WordPress manage the document title
        add_theme_support('title-tag');

        // Enable support for Post Thumbnails on posts and pages
        add_theme_support('post-thumbnails');

        // Add support for responsive embeds
        add_theme_support('responsive-embeds');

        // Add support for full and wide align images
        add_theme_support('align-wide');

        // Add support for editor styles
        add_theme_support('editor-styles');

        // Add support for custom logo
        add_theme_support(
            'custom-logo',
            array(
                'height'      => 100,
                'width'       => 400,
                'flex-width'  => true,
                'flex-height' => true,
            )
        );

        // Register nav menus
        register_nav_menus(
            array(
                'primary' => esc_html__('Primary Menu', 'quill'),
                'footer'  => esc_html__('Footer Menu', 'quill'),
            )
        );

        // Add theme support for selective refresh for widgets
        add_theme_support('customize-selective-refresh-widgets');

        // Add support for Block Styles
        add_theme_support('wp-block-styles');

        // Add support for editor color palette
        add_theme_support(
            'editor-color-palette',
            array(
                array(
                    'name'  => esc_html__('Primary', 'quill'),
                    'slug'  => 'primary',
                    'color' => '#0073aa',
                ),
                array(
                    'name'  => esc_html__('Secondary', 'quill'),
                    'slug'  => 'secondary',
                    'color' => '#23282d',
                ),
                array(
                    'name'  => esc_html__('Background', 'quill'),
                    'slug'  => 'background',
                    'color' => '#ffffff',
                ),
                array(
                    'name'  => esc_html__('Text', 'quill'),
                    'slug'  => 'text',
                    'color' => '#23282d',
                ),
            )
        );

        // Add support for font sizes
        add_theme_support(
            'editor-font-sizes',
            array(
                array(
                    'name'      => esc_html__('Small', 'quill'),
                    'shortName' => esc_html_x('S', 'Font size', 'quill'),
                    'size'      => 14,
                    'slug'      => 'small',
                ),
                array(
                    'name'      => esc_html__('Normal', 'quill'),
                    'shortName' => esc_html_x('M', 'Font size', 'quill'),
                    'size'      => 16,
                    'slug'      => 'normal',
                ),
                array(
                    'name'      => esc_html__('Large', 'quill'),
                    'shortName' => esc_html_x('L', 'Font size', 'quill'),
                    'size'      => 20,
                    'slug'      => 'large',
                ),
                array(
                    'name'      => esc_html__('Huge', 'quill'),
                    'shortName' => esc_html_x('XL', 'Font size', 'quill'),
                    'size'      => 24,
                    'slug'      => 'huge',
                ),
            )
        );
    }

    /**
     * Force HTTPS for all URLs
     */
    public function force_https_urls($content)
    {
        if (is_ssl()) {
            $content = str_replace('http://', 'https://', $content);
        }
        return $content;
    }

    /**
     * Add AMP body classes
     */
    public function add_amp_body_classes($classes)
    {
        $classes[] = 'amp';
        if (is_singular('recipe')) {
            $classes[] = 'amp-recipe';
        }
        return $classes;
    }
}
