<?php

/**
 * Template part for displaying recipe content
 *
 * @package Quill
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('recipe-article'); ?>>
    <?php if (! quill_is_amp()) : ?>
        <?php quill_adsense_ad('recipe_top'); ?>
    <?php endif; ?>

    <header class="entry-header">
        <?php if (has_post_thumbnail()) : ?>
            <div class="recipe-featured-image">
                <?php if (quill_is_amp()) : ?>
                    <amp-img src="<?php the_post_thumbnail_url('quill-recipe'); ?>"
                        width="800"
                        height="600"
                        layout="responsive"
                        alt="<?php the_title_attribute(); ?>">
                    </amp-img>
                <?php else : ?>
                    <?php the_post_thumbnail('quill-recipe', array('class' => 'recipe-image')); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

        <div class="entry-meta">
            <?php quill_posted_on(); ?>
        </div>
    </header>

    <div class="recipe-content">
        <div class="recipe-summary">
            <?php the_excerpt(); ?>
        </div>

        <?php quill_recipe_meta(); ?>

        <?php if (! quill_is_amp()) : ?>
            <?php quill_adsense_ad('content'); ?>
        <?php endif; ?>

        <div class="recipe-main">
            <div class="recipe-ingredients-wrapper">
                <?php quill_recipe_ingredients(); ?>
            </div>

            <div class="recipe-instructions-wrapper">
                <?php quill_recipe_instructions(); ?>
            </div>

            <?php if (! quill_is_amp()) : ?>
                <?php quill_adsense_ad('recipe_bottom'); ?>
            <?php endif; ?>

            <?php if ($notes = quill_get_recipe_notes()) : ?>
                <div class="recipe-notes-wrapper">
                    <?php quill_recipe_notes(); ?>
                </div>
            <?php endif; ?>

            <?php if ($nutrition = quill_get_recipe_nutrition()) : ?>
                <div class="recipe-nutrition-wrapper">
                    <?php quill_recipe_nutrition(); ?>
                </div>
            <?php endif; ?>
        </div>

        <footer class="entry-footer">
            <?php quill_entry_footer(); ?>
        </footer>
    </div>

    <?php if (! quill_is_amp() && comments_open()) : ?>
        <div class="recipe-comments">
            <?php comments_template(); ?>
        </div>
    <?php endif; ?>
</article>