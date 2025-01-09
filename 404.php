<?php

/**
 * The template for displaying 404 pages (not found)
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
        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Oops! That page can&rsquo;t be found.', 'quill'); ?></h1>
            </header>

            <div class="page-content">
                <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'quill'); ?></p>

                <?php get_search_form(); ?>

                <div class="widget-area">
                    <?php
                    the_widget(
                        'WP_Widget_Recent_Posts',
                        array(
                            'title'     => esc_html__('Recent Posts', 'quill'),
                            'number'    => 5,
                            'show_date' => true,
                        )
                    );
                    ?>

                    <div class="widget widget_categories">
                        <h2 class="widget-title"><?php esc_html_e('Most Used Categories', 'quill'); ?></h2>
                        <ul>
                            <?php
                            wp_list_categories(
                                array(
                                    'orderby'    => 'count',
                                    'order'      => 'DESC',
                                    'show_count' => 1,
                                    'title_li'   => '',
                                    'number'     => 10,
                                )
                            );
                            ?>
                        </ul>
                    </div>

                    <?php
                    /* translators: %1$s: smiley */
                    $archive_content = '<p>' . sprintf(esc_html__('Try looking in the monthly archives. %1$s', 'quill'), convert_smilies(':)')) . '</p>';
                    the_widget(
                        'WP_Widget_Archives',
                        array(
                            'title'     => esc_html__('Archives', 'quill'),
                            'count'     => 0,
                            'dropdown'  => 1,
                        ),
                        array(
                            'after_title' => '</h2>' . $archive_content,
                        )
                    );
                    ?>
                </div>
            </div>
        </section>
    </main>
</div>

<?php
get_footer();
