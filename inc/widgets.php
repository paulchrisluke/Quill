<?php

/**
 * Register custom widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recipe Categories Widget
 */
class Quill_Recipe_Categories_Widget extends WP_Widget
{
    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'quill_recipe_categories',
            __('Recipe Categories', 'quill'),
            array('description' => __('Display recipe categories with post counts.', 'quill'))
        );
    }

    /**
     * Front-end display of widget.
     */
    public function widget($args, $instance)
    {
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : __('Recipe Categories', 'quill');
        $show_count = !empty($instance['show_count']) ? (bool) $instance['show_count'] : false;
        $show_hierarchy = !empty($instance['show_hierarchy']) ? (bool) $instance['show_hierarchy'] : true;

        echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $categories = get_terms(array(
            'taxonomy' => 'course',
            'hide_empty' => true,
            'parent' => 0,
        ));

        if (!empty($categories) && !is_wp_error($categories)) {
            echo '<ul class="recipe-categories">';
            foreach ($categories as $category) {
                $this->display_category($category, $show_count, $show_hierarchy);
            }
            echo '</ul>';
        }

        echo $args['after_widget'];
    }

    /**
     * Display a single category and its children
     */
    private function display_category($category, $show_count, $show_hierarchy)
    {
        echo '<li class="recipe-category">';
        echo '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name);
        if ($show_count) {
            echo ' <span class="count">(' . esc_html($category->count) . ')</span>';
        }
        echo '</a>';

        if ($show_hierarchy) {
            $children = get_terms(array(
                'taxonomy' => 'course',
                'hide_empty' => true,
                'parent' => $category->term_id,
            ));

            if (!empty($children) && !is_wp_error($children)) {
                echo '<ul class="children">';
                foreach ($children as $child) {
                    $this->display_category($child, $show_count, $show_hierarchy);
                }
                echo '</ul>';
            }
        }
        echo '</li>';
    }

    /**
     * Back-end widget form.
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Recipe Categories', 'quill');
        $show_count = !empty($instance['show_count']) ? (bool) $instance['show_count'] : false;
        $show_hierarchy = !empty($instance['show_hierarchy']) ? (bool) $instance['show_hierarchy'] : true;
?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'quill'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id('show_count')); ?>" name="<?php echo esc_attr($this->get_field_name('show_count')); ?>" <?php checked($show_count); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_count')); ?>"><?php _e('Show post counts', 'quill'); ?></label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo esc_attr($this->get_field_id('show_hierarchy')); ?>" name="<?php echo esc_attr($this->get_field_name('show_hierarchy')); ?>" <?php checked($show_hierarchy); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_hierarchy')); ?>"><?php _e('Show hierarchy', 'quill'); ?></label>
        </p>
<?php
    }

    /**
     * Sanitize widget form values as they are saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['show_count'] = (!empty($new_instance['show_count'])) ? 1 : 0;
        $instance['show_hierarchy'] = (!empty($new_instance['show_hierarchy'])) ? 1 : 0;

        return $instance;
    }
}

/**
 * Register Widgets
 */
function quill_register_widgets()
{
    register_widget('Quill_Recipe_Categories_Widget');
}
add_action('widgets_init', 'quill_register_widgets');

/**
 * Register widget areas
 */
function quill_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Sidebar', 'quill'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'quill'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer', 'quill'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your footer.', 'quill'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'quill_widgets_init');
