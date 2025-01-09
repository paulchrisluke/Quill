<?php

/**
 * Register custom post types
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the Recipe post type
 */
function quill_register_recipe_post_type()
{
    $labels = array(
        'name'                  => _x('Recipes', 'Post type general name', 'quill'),
        'singular_name'         => _x('Recipe', 'Post type singular name', 'quill'),
        'menu_name'            => _x('Recipes', 'Admin Menu text', 'quill'),
        'name_admin_bar'        => _x('Recipe', 'Add New on Toolbar', 'quill'),
        'add_new'              => __('Add New', 'quill'),
        'add_new_item'          => __('Add New Recipe', 'quill'),
        'new_item'             => __('New Recipe', 'quill'),
        'edit_item'            => __('Edit Recipe', 'quill'),
        'view_item'            => __('View Recipe', 'quill'),
        'all_items'            => __('All Recipes', 'quill'),
        'search_items'         => __('Search Recipes', 'quill'),
        'parent_item_colon'     => __('Parent Recipes:', 'quill'),
        'not_found'            => __('No recipes found.', 'quill'),
        'not_found_in_trash'    => __('No recipes found in Trash.', 'quill'),
        'featured_image'        => _x('Recipe Cover Image', 'Overrides the "Featured Image" phrase', 'quill'),
        'set_featured_image'    => _x('Set cover image', 'Overrides the "Set featured image" phrase', 'quill'),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase', 'quill'),
        'use_featured_image'    => _x('Use as cover image', 'Overrides the "Use as featured image" phrase', 'quill'),
        'archives'             => _x('Recipe archives', 'The post type archive label used in nav menus', 'quill'),
        'insert_into_item'      => _x('Insert into recipe', 'Overrides the "Insert into post" phrase', 'quill'),
        'uploaded_to_this_item' => _x('Uploaded to this recipe', 'Overrides the "Uploaded to this post" phrase', 'quill'),
        'filter_items_list'     => _x('Filter recipes list', 'Screen reader text for the filter links', 'quill'),
        'items_list_navigation' => _x('Recipes list navigation', 'Screen reader text for the pagination', 'quill'),
        'items_list'           => _x('Recipes list', 'Screen reader text for the items list', 'quill'),
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
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-food',
        'supports'           => array(
            'title',
            'editor',
            'author',
            'thumbnail',
            'excerpt',
            'comments',
            'custom-fields',
            'revisions'
        ),
        'show_in_rest'       => true,
    );

    register_post_type('recipe', $args);
}
add_action('init', 'quill_register_recipe_post_type');

/**
 * Register Recipe taxonomies
 */
function quill_register_recipe_taxonomies()
{
    // Course Taxonomy
    $course_labels = array(
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
        'menu_name'         => __('Course', 'quill'),
    );

    register_taxonomy('course', 'recipe', array(
        'hierarchical'      => true,
        'labels'           => $course_labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'course'),
        'show_in_rest'     => true,
    ));

    // Cuisine Taxonomy
    $cuisine_labels = array(
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
        'menu_name'         => __('Cuisine', 'quill'),
    );

    register_taxonomy('cuisine', 'recipe', array(
        'hierarchical'      => true,
        'labels'           => $cuisine_labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'cuisine'),
        'show_in_rest'     => true,
    ));

    // Dietary Requirements Taxonomy
    $dietary_labels = array(
        'name'              => _x('Dietary Requirements', 'taxonomy general name', 'quill'),
        'singular_name'     => _x('Dietary Requirement', 'taxonomy singular name', 'quill'),
        'search_items'      => __('Search Dietary Requirements', 'quill'),
        'all_items'         => __('All Dietary Requirements', 'quill'),
        'parent_item'       => __('Parent Dietary Requirement', 'quill'),
        'parent_item_colon' => __('Parent Dietary Requirement:', 'quill'),
        'edit_item'         => __('Edit Dietary Requirement', 'quill'),
        'update_item'       => __('Update Dietary Requirement', 'quill'),
        'add_new_item'      => __('Add New Dietary Requirement', 'quill'),
        'new_item_name'     => __('New Dietary Requirement Name', 'quill'),
        'menu_name'         => __('Dietary', 'quill'),
    );

    register_taxonomy('dietary', 'recipe', array(
        'hierarchical'      => false,
        'labels'           => $dietary_labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'dietary'),
        'show_in_rest'     => true,
    ));
}
add_action('init', 'quill_register_recipe_taxonomies');
