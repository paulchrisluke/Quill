<?php

/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package Quill
 */
?>

<section class="no-results not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e('Nothing Found', 'quill'); ?></h1>
    </header>

    <div class="page-content">
        <?php
        if (is_home() && current_user_can('publish_posts')) :
            printf(
                '<p>' . wp_kses(
                    /* translators: 1: link to WP admin new post page. */
                    __('Ready to publish your first recipe? <a href="%1$s">Get started here</a>.', 'quill'),
                    array(
                        'a' => array(
                            'href' => array(),
                        ),
                    )
                ) . '</p>',
                esc_url(admin_url('post-new.php?post_type=recipe'))
            );
        elseif (is_search()) :
        ?>
            <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'quill'); ?></p>
            <?php
            get_search_form();

            // Display popular recipes
            $popular_recipes = new WP_Query(array(
                'post_type' => 'recipe',
                'posts_per_page' => 6,
                'orderby' => 'comment_count',
                'order' => 'DESC',
            ));

            if ($popular_recipes->have_posts()) :
            ?>
                <div class="popular-recipes">
                    <h2><?php esc_html_e('Popular Recipes', 'quill'); ?></h2>
                    <div class="recipe-grid">
                        <?php
                        while ($popular_recipes->have_posts()) :
                            $popular_recipes->the_post();
                            get_template_part('template-parts/content', 'recipe');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            <?php
            endif;

        elseif (is_archive()) :
            ?>
            <p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'quill'); ?></p>
            <?php
            get_search_form();

            // Display recent recipes
            $recent_recipes = new WP_Query(array(
                'post_type' => 'recipe',
                'posts_per_page' => 6,
                'orderby' => 'date',
                'order' => 'DESC',
            ));

            if ($recent_recipes->have_posts()) :
            ?>
                <div class="recent-recipes">
                    <h2><?php esc_html_e('Recent Recipes', 'quill'); ?></h2>
                    <div class="recipe-grid">
                        <?php
                        while ($recent_recipes->have_posts()) :
                            $recent_recipes->the_post();
                            get_template_part('template-parts/content', 'recipe');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            <?php
            endif;

        else :
            ?>
            <p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'quill'); ?></p>
        <?php
            get_search_form();
        endif;
        ?>

        <div class="recipe-categories">
            <h2><?php esc_html_e('Browse Recipe Categories', 'quill'); ?></h2>
            <?php
            wp_list_categories(array(
                'taxonomy' => 'recipe_category',
                'title_li' => '',
                'show_count' => true,
                'hierarchical' => true,
            ));
            ?>
        </div>
    </div>
</section>