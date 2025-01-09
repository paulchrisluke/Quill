<?php

/**
 * The template for displaying search results pages
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
                <h1 class="page-title">
                    <?php
                    /* translators: %s: search query */
                    printf(esc_html__('Search Results for: %s', 'quill'), '<span>' . get_search_query() . '</span>');
                    ?>
                </h1>
            </header>

        <?php
            /* Start the Loop */
            while (have_posts()) :
                the_post();

                /**
                 * Run the loop for the search to output the results.
                 * If you want to overload this in a child theme then include a file
                 * called content-search.php and that will be used instead.
                 */
                get_template_part('template-parts/content', 'search');

                // Show ad after every third post
                if (0 === $wp_query->current_post % 3) {
                    get_template_part('template-parts/ads/adsense', 'search');
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
