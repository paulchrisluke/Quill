<?php

/**
 * The template for displaying archive pages
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php if (have_posts()) : ?>
            <header class="page-header">
                <?php
                the_archive_title('<h1 class="page-title">', '</h1>');
                the_archive_description('<div class="archive-description">', '</div>');
                ?>
            </header>

        <?php
            /* Start the Loop */
            while (have_posts()) :
                the_post();

                /*
                 * Include the Post-Type-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
                 */
                get_template_part('template-parts/content', get_post_type());

                // Show ad after every third post
                if (! quill_is_amp() && 0 === $wp_query->current_post % 3) {
                    quill_adsense_ad('content');
                }

            endwhile;

            the_posts_navigation(
                array(
                    'prev_text' => esc_html__('← Older posts', 'quill'),
                    'next_text' => esc_html__('Newer posts →', 'quill'),
                )
            );

        else :

            get_template_part('template-parts/content', 'none');

        endif;
        ?>
    </main>
</div>

<?php
get_sidebar();
get_footer();
