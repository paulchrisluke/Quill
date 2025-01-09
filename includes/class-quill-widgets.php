<?php

/**
 * Widget areas registration
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

class Quill_Widgets
{
    /**
     * Register widget areas
     */
    public static function init()
    {
        add_action('widgets_init', array(__CLASS__, 'register_widget_areas'));
    }

    /**
     * Register widget areas
     */
    public static function register_widget_areas()
    {
        // Header Ad Area
        register_sidebar(array(
            'name'          => esc_html__('Header Advertisement', 'quill'),
            'id'            => 'header-ad',
            'description'   => esc_html__('Add header advertisement widgets here.', 'quill'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title screen-reader-text">',
            'after_title'   => '</h2>',
        ));

        // Recipe Top Ad Area
        register_sidebar(array(
            'name'          => esc_html__('Recipe Top Advertisement', 'quill'),
            'id'            => 'recipe-top-ad',
            'description'   => esc_html__('Add advertisement widgets at the top of recipe posts.', 'quill'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title screen-reader-text">',
            'after_title'   => '</h2>',
        ));

        // Recipe Middle Ad Area
        register_sidebar(array(
            'name'          => esc_html__('Recipe Middle Advertisement', 'quill'),
            'id'            => 'recipe-middle-ad',
            'description'   => esc_html__('Add advertisement widgets in the middle of recipe posts.', 'quill'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title screen-reader-text">',
            'after_title'   => '</h2>',
        ));

        // Recipe Bottom Ad Area
        register_sidebar(array(
            'name'          => esc_html__('Recipe Bottom Advertisement', 'quill'),
            'id'            => 'recipe-bottom-ad',
            'description'   => esc_html__('Add advertisement widgets at the bottom of recipe posts.', 'quill'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title screen-reader-text">',
            'after_title'   => '</h2>',
        ));

        // Sidebar
        register_sidebar(array(
            'name'          => esc_html__('Sidebar', 'quill'),
            'id'            => 'sidebar-1',
            'description'   => esc_html__('Add widgets here.', 'quill'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
    }
}

// Initialize widgets
Quill_Widgets::init();
