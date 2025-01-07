<?php get_header(); ?>

<main id="primary" class="site-main">
    <?php while (have_posts()): ?>
        <?php the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                <div class="entry-meta">
                    <?php
                    printf(
                        '<span class="posted-on">%s</span>',
                        get_the_date()
                    );
                    ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()): ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail('large', array('layout' => 'responsive')); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                // Get recipe meta data
                $ingredients = get_post_meta(get_the_ID(), '_recipe_ingredients', true);
                $instructions = get_post_meta(get_the_ID(), '_recipe_instructions', true);
                $cook_time = get_post_meta(get_the_ID(), '_recipe_cook_time', true);
                $prep_time = get_post_meta(get_the_ID(), '_recipe_prep_time', true);

                // Output recipe schema
                if ($ingredients || $instructions):
                ?>
                    <script type="application/ld+json">
                        {
                            "@context": "https://schema.org/",
                            "@type": "Recipe",
                            "name": "<?php echo esc_js(get_the_title()); ?>",
                            "author": {
                                "@type": "Person",
                                "name": "<?php echo esc_js(get_the_author()); ?>"
                            },
                            "datePublished": "<?php echo get_the_date('c'); ?>",
                            "description": "<?php echo esc_js(get_the_excerpt()); ?>",
                            <?php if (has_post_thumbnail()): ?> "image": "<?php echo esc_url(get_the_post_thumbnail_url(null, 'full')); ?>",
                            <?php endif; ?>
                            <?php if ($prep_time): ?> "prepTime": "<?php echo esc_js($prep_time); ?>",
                            <?php endif; ?>
                            <?php if ($cook_time): ?> "cookTime": "<?php echo esc_js($cook_time); ?>",
                            <?php endif; ?>
                            <?php if ($ingredients): ?> "recipeIngredient": <?php echo json_encode(explode("\n", $ingredients)); ?>,
                            <?php endif; ?>
                            <?php if ($instructions): ?> "recipeInstructions": <?php echo json_encode(explode("\n", $instructions)); ?>,
                            <?php endif; ?> "nutrition": {
                                "@type": "NutritionInformation"
                            }
                        }
                    </script>

                    <div class="recipe-content">
                        <?php if ($prep_time || $cook_time): ?>
                            <div class="recipe-times">
                                <?php if ($prep_time): ?>
                                    <div class="prep-time">
                                        <strong>Prep Time:</strong> <?php echo esc_html($prep_time); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($cook_time): ?>
                                    <div class="cook-time">
                                        <strong>Cook Time:</strong> <?php echo esc_html($cook_time); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($ingredients): ?>
                            <div class="recipe-ingredients">
                                <h2>Ingredients</h2>
                                <ul>
                                    <?php
                                    $ingredients_array = explode("\n", $ingredients);
                                    foreach ($ingredients_array as $ingredient):
                                    ?>
                                        <li><?php echo esc_html(trim($ingredient)); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($instructions): ?>
                            <div class="recipe-instructions">
                                <h2>Instructions</h2>
                                <ol>
                                    <?php
                                    $instructions_array = explode("\n", $instructions);
                                    foreach ($instructions_array as $instruction):
                                    ?>
                                        <li><?php echo esc_html(trim($instruction)); ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php the_content(); ?>
            </div>

            <footer class="entry-footer">
                <?php
                $categories_list = get_the_category_list(', ');
                if ($categories_list):
                ?>
                    <span class="cat-links">
                        <?php echo $categories_list; ?>
                    </span>
                <?php endif; ?>

                <?php
                $tags_list = get_the_tag_list('', ', ');
                if ($tags_list):
                ?>
                    <span class="tags-links">
                        <?php echo $tags_list; ?>
                    </span>
                <?php endif; ?>
            </footer>
        </article>

        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        if (comments_open() || get_comments_number()):
            comments_template();
        endif;
        ?>

    <?php endwhile; ?>
</main>

<?php get_sidebar(); ?>
<?php get_footer(); ?>