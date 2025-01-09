<?php

/**
 * Template for displaying single recipe posts
 */

get_header('amp');
?>

<main id="primary" class="site-main">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('recipe-post'); ?>>
            <header class="entry-header">
                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                <div class="recipe-meta">
                    <span class="cooking-time">
                        <?php echo esc_html(get_post_meta(get_the_ID(), 'cooking_time', true)); ?> mins
                    </span>
                    <span class="servings">
                        <?php echo esc_html(get_post_meta(get_the_ID(), 'servings', true)); ?> servings
                    </span>
                    <span class="difficulty">
                        <?php echo esc_html(get_post_meta(get_the_ID(), 'difficulty', true)); ?>
                    </span>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="recipe-featured-image">
                    <?php
                    $thumbnail_id = get_post_thumbnail_id();
                    $thumbnail_data = wp_get_attachment_image_src($thumbnail_id, 'full');
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

            <?php if (get_theme_mod('quill_adsense_recipe_top_enabled', true)) : ?>
                <div class="ad-container ad-recipe-top">
                    <amp-ad
                        type="adsense"
                        layout="responsive"
                        width="300"
                        height="250"
                        data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id')); ?>"
                        data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_recipe_top_slot')); ?>">
                    </amp-ad>
                </div>
            <?php endif; ?>

            <div class="recipe-description">
                <?php the_content(); ?>
            </div>

            <div class="recipe-ingredients">
                <h2><?php esc_html_e('Ingredients', 'quill'); ?></h2>
                <?php
                $ingredients = get_post_meta(get_the_ID(), 'ingredients', true);
                if (!empty($ingredients)) :
                    echo '<ul class="ingredients-list">';
                    foreach ($ingredients as $ingredient) {
                        echo '<li>' . esc_html($ingredient) . '</li>';
                    }
                    echo '</ul>';
                endif;
                ?>
            </div>

            <?php if (get_theme_mod('quill_adsense_recipe_middle_enabled', true)) : ?>
                <div class="ad-container ad-recipe-middle">
                    <amp-ad
                        type="adsense"
                        layout="responsive"
                        width="300"
                        height="250"
                        data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id')); ?>"
                        data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_recipe_middle_slot')); ?>">
                    </amp-ad>
                </div>
            <?php endif; ?>

            <div class="recipe-instructions">
                <h2><?php esc_html_e('Instructions', 'quill'); ?></h2>
                <?php
                $instructions = get_post_meta(get_the_ID(), 'instructions', true);
                if (!empty($instructions)) :
                    echo '<ol class="instructions-list">';
                    foreach ($instructions as $instruction) {
                        echo '<li>' . wp_kses_post($instruction) . '</li>';
                    }
                    echo '</ol>';
                endif;
                ?>
            </div>

            <footer class="entry-footer">
                <?php
                $categories_list = get_the_category_list(', ');
                if ($categories_list) {
                    printf('<span class="cat-links">%s</span>', $categories_list);
                }

                $tags_list = get_the_tag_list('', ', ');
                if ($tags_list) {
                    printf('<span class="tags-links">%s</span>', $tags_list);
                }
                ?>
            </footer>
        </article>

        <?php if (get_theme_mod('quill_adsense_recipe_bottom_enabled', true)) : ?>
            <div class="ad-container ad-recipe-bottom">
                <amp-ad
                    type="adsense"
                    layout="responsive"
                    width="300"
                    height="250"
                    data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id')); ?>"
                    data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_recipe_bottom_slot')); ?>">
                </amp-ad>
            </div>
        <?php endif; ?>

        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>

    <?php endwhile; ?>
</main>

<?php
get_footer('amp');
?>