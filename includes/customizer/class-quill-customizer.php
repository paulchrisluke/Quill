<?php

/**
 * Quill Theme Customizer
 *
 * @package Quill
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quill Theme Customizer class
 */
class Quill_Customizer
{
    /**
     * Initialize the class
     */
    public function init()
    {
        add_action('customize_register', array($this, 'register'));
        add_action('customize_preview_init', array($this, 'preview_js'));
    }

    /**
     * Register customizer options
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     */
    public function register($wp_customize)
    {
        // Add theme options panel
        $wp_customize->add_panel(
            'quill_theme_options',
            array(
                'title'       => esc_html__('Theme Options', 'quill'),
                'priority'    => 130,
                'capability'  => 'edit_theme_options',
            )
        );

        // Add color section
        $wp_customize->add_section(
            'quill_colors',
            array(
                'title'       => esc_html__('Colors', 'quill'),
                'panel'       => 'quill_theme_options',
                'priority'    => 10,
                'capability'  => 'edit_theme_options',
            )
        );

        // Add primary color setting
        $wp_customize->add_setting(
            'quill_primary_color',
            array(
                'default'           => '#0073aa',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'postMessage',
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'quill_primary_color',
                array(
                    'label'    => esc_html__('Primary Color', 'quill'),
                    'section'  => 'quill_colors',
                    'settings' => 'quill_primary_color',
                )
            )
        );

        // Add AdSense section
        $wp_customize->add_section(
            'quill_adsense',
            array(
                'title'       => esc_html__('AdSense Settings', 'quill'),
                'panel'       => 'quill_theme_options',
                'priority'    => 20,
                'capability'  => 'edit_theme_options',
            )
        );

        // Add AdSense publisher ID setting
        $wp_customize->add_setting(
            'quill_adsense_publisher_id',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        $wp_customize->add_control(
            'quill_adsense_publisher_id',
            array(
                'label'       => esc_html__('Publisher ID', 'quill'),
                'description' => esc_html__('Enter your AdSense publisher ID (e.g., pub-1234567890)', 'quill'),
                'section'     => 'quill_adsense',
                'type'        => 'text',
            )
        );

        // Add AdSense ad slots
        $ad_slots = array(
            'header'        => esc_html__('Header Ad Slot', 'quill'),
            'sidebar'       => esc_html__('Sidebar Ad Slot', 'quill'),
            'content'       => esc_html__('Content Ad Slot', 'quill'),
            'recipe_top'    => esc_html__('Recipe Top Ad Slot', 'quill'),
            'recipe_bottom' => esc_html__('Recipe Bottom Ad Slot', 'quill'),
        );

        foreach ($ad_slots as $slot => $label) {
            $wp_customize->add_setting(
                'quill_adsense_slot_' . $slot,
                array(
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            );

            $wp_customize->add_control(
                'quill_adsense_slot_' . $slot,
                array(
                    'label'       => $label,
                    'description' => esc_html__('Enter your ad slot ID', 'quill'),
                    'section'     => 'quill_adsense',
                    'type'        => 'text',
                )
            );
        }

        // Add layout section
        $wp_customize->add_section(
            'quill_layout',
            array(
                'title'       => esc_html__('Layout', 'quill'),
                'panel'       => 'quill_theme_options',
                'priority'    => 30,
                'capability'  => 'edit_theme_options',
            )
        );

        // Add sidebar position setting
        $wp_customize->add_setting(
            'quill_sidebar_position',
            array(
                'default'           => 'right',
                'sanitize_callback' => array($this, 'sanitize_select'),
            )
        );

        $wp_customize->add_control(
            'quill_sidebar_position',
            array(
                'label'    => esc_html__('Sidebar Position', 'quill'),
                'section'  => 'quill_layout',
                'type'     => 'select',
                'choices'  => array(
                    'left'  => esc_html__('Left', 'quill'),
                    'right' => esc_html__('Right', 'quill'),
                    'none'  => esc_html__('No Sidebar', 'quill'),
                ),
            )
        );

        // Add recipe layout setting
        $wp_customize->add_setting(
            'quill_recipe_layout',
            array(
                'default'           => 'grid',
                'sanitize_callback' => array($this, 'sanitize_select'),
            )
        );

        $wp_customize->add_control(
            'quill_recipe_layout',
            array(
                'label'    => esc_html__('Recipe Archive Layout', 'quill'),
                'section'  => 'quill_layout',
                'type'     => 'select',
                'choices'  => array(
                    'grid'   => esc_html__('Grid', 'quill'),
                    'list'   => esc_html__('List', 'quill'),
                    'masonry' => esc_html__('Masonry', 'quill'),
                ),
            )
        );

        // Add footer section
        $wp_customize->add_section(
            'quill_footer',
            array(
                'title'       => esc_html__('Footer', 'quill'),
                'panel'       => 'quill_theme_options',
                'priority'    => 40,
                'capability'  => 'edit_theme_options',
            )
        );

        // Add copyright text setting
        $wp_customize->add_setting(
            'quill_copyright_text',
            array(
                'default'           => sprintf(
                    /* translators: %s: Current year */
                    esc_html__('© %s. All rights reserved.', 'quill'),
                    date_i18n('Y')
                ),
                'sanitize_callback' => 'wp_kses_post',
                'transport'         => 'postMessage',
            )
        );

        $wp_customize->add_control(
            'quill_copyright_text',
            array(
                'label'    => esc_html__('Copyright Text', 'quill'),
                'section'  => 'quill_footer',
                'type'     => 'textarea',
            )
        );
    }

    /**
     * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
     */
    public function preview_js()
    {
        wp_enqueue_script(
            'quill-customizer',
            QUILL_URI . '/assets/js/customizer.js',
            array('customize-preview'),
            QUILL_VERSION,
            true
        );
    }

    /**
     * Sanitize select field
     *
     * @param string $input   Select field input.
     * @param object $setting Setting object.
     * @return string Sanitized value.
     */
    public function sanitize_select($input, $setting)
    {
        // Get list of choices from the control associated with the setting.
        $choices = $setting->manager->get_control($setting->id)->choices;

        // Return input if valid or return default option.
        return (array_key_exists($input, $choices) ? $input : $setting->default);
    }
}
