<?php

/**
 * The template for displaying recipe archives
 *
 * @package Quill
 */

get_header('amp');

// Get sidebar position
$sidebar_position = get_theme_mod('quill_recipe_sidebar_position', 'right');
$container_class = 'content-area';
if ($sidebar_position !== 'none') {
    $container_class .= ' has-sidebar sidebar-' . $sidebar_position;
}
?>

<div id="primary" class="<?php echo esc_attr($container_class); ?>">
    <main id="main" class="site-main">
        <?php if (have_posts()) : ?>
            <header class="page-header">
                <?php
                the_archive_title('<h1 class="page-title">', '</h1>');
                the_archive_description('<div class="archive-description">', '</div>');
                ?>
            </header>

            <div class="recipe-filters">
                <?php
                // Course filter
                $courses = get_terms(array(
                    'taxonomy' => 'course',
                    'hide_empty' => true,
                ));
                if (!empty($courses) && !is_wp_error($courses)) :
                ?>
                    <div class="filter-group">
                        <h3><?php _e('Filter by Course', 'quill'); ?></h3>
                        <div class="filter-options">
                            <?php foreach ($courses as $course) : ?>
                                <a href="<?php echo esc_url(get_term_link($course)); ?>" class="filter-option">
                                    <?php echo esc_html($course->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Cuisine filter
                $cuisines = get_terms(array(
                    'taxonomy' => 'cuisine',
                    'hide_empty' => true,
                ));
                if (!empty($cuisines) && !is_wp_error($cuisines)) :
                ?>
                    <div class="filter-group">
                        <h3><?php _e('Filter by Cuisine', 'quill'); ?></h3>
                        <div class="filter-options">
                            <?php foreach ($cuisines as $cuisine) : ?>
                                <a href="<?php echo esc_url(get_term_link($cuisine)); ?>" class="filter-option">
                                    <?php echo esc_html($cuisine->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Dietary filter
                $dietary_requirements = get_terms(array(
                    'taxonomy' => 'dietary',
                    'hide_empty' => true,
                ));
                if (!empty($dietary_requirements) && !is_wp_error($dietary_requirements)) :
                ?>
                    <div class="filter-group">
                        <h3><?php _e('Filter by Dietary Requirements', 'quill'); ?></h3>
                        <div class="filter-options">
                            <?php foreach ($dietary_requirements as $dietary) : ?>
                                <a href="<?php echo esc_url(get_term_link($dietary)); ?>" class="filter-option">
                                    <?php echo esc_html($dietary->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (get_theme_mod('quill_adsense_archive_top_enabled', true)) : ?>
                <div class="ad-container ad-archive-top">
                    <amp-ad
                        type="adsense"
                        layout="responsive"
                        width="728"
                        height="90"
                        data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id')); ?>"
                        data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_archive_top_slot')); ?>">
                    </amp-ad>
                </div>
            <?php endif; ?>

            <div class="recipes-grid">
                <?php
                while (have_posts()) :
                    the_post();
                ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('recipe-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="recipe-thumbnail">
                                <?php
                                $thumbnail_id = get_post_thumbnail_id();
                                $thumbnail_data = wp_get_attachment_image_src($thumbnail_id, 'medium');
                                if ($thumbnail_data) :
                                ?>
                                    <amp-img
                                        src="<?php echo esc_url($thumbnail_data[0]); ?>"
                                        width="<?php echo absint($thumbnail_data[1]); ?>"
                                        height="<?php echo absint($thumbnail_data[2]); ?>"
                                        layout="responsive"
                                        alt="<?php the_title_attribute(); ?>">
                                    </amp-img>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="recipe-card-content">
                            <header class="entry-header">
                                <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>

                                <div class="recipe-meta">
                                    <span class="cooking-time">
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'cooking_time', true)); ?> mins
                                    </span>
                                    <span class="difficulty">
                                        <?php echo esc_html(get_post_meta(get_the_ID(), 'difficulty', true)); ?>
                                    </span>
                                </div>
                            </header>

                            <div class="entry-summary">
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </article>

                    <?php
                    // Insert ad after every 6 recipes
                    if (0 === $wp_query->current_post % 6 && get_theme_mod('quill_adsense_archive_between_enabled', true)) :
                    ?>
                        <div class="ad-container ad-archive-between">
                            <amp-ad
                                type="adsense"
                                layout="responsive"
                                width="300"
                                height="250"
                                data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id')); ?>"
                                data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_archive_between_slot')); ?>">
                            </amp-ad>
                        </div>
                    <?php endif; ?>

                <?php endwhile; ?>
            </div>

        <?php
            the_posts_pagination(array(
                'prev_text' => __('Previous page', 'quill'),
                'next_text' => __('Next page', 'quill'),
            ));

        else :
        ?>
            <p><?php esc_html_e('No recipes found.', 'quill'); ?></p>
        <?php endif; ?>
    </main>
</div>

<?php
if ('none' !== $sidebar_position) {
    get_sidebar('recipe');
}
get_footer('amp');
