<?php

/**
 * The template for displaying all single posts
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
        <?php
        while (have_posts()) :
            the_post();
        ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                    <div class="entry-meta">
                        <?php
                        echo '<span class="posted-on">' . get_the_date() . '</span>';
                        ?>
                    </div>
                </header>

                <?php
                if (has_post_thumbnail()) :
                    $thumbnail_id = get_post_thumbnail_id();
                    $img_src = wp_get_attachment_image_src($thumbnail_id, 'full');
                    if ($img_src) :
                ?>
                        <div class="post-thumbnail">
                            <amp-img
                                src="<?php echo esc_url($img_src[0]); ?>"
                                width="<?php echo esc_attr($img_src[1]); ?>"
                                height="<?php echo esc_attr($img_src[2]); ?>"
                                layout="responsive"
                                alt="<?php echo esc_attr(get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)); ?>">
                            </amp-img>
                        </div>
                <?php
                    endif;
                endif;
                ?>

                <div class="entry-content">
                    <?php
                    the_content();

                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'quill'),
                        'after'  => '</div>',
                    ));
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

        <?php
            // Display AdSense ad
            get_template_part('template-parts/ads/adsense', 'content');

            // Post navigation
            the_post_navigation(array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'quill') . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'quill') . '</span> <span class="nav-title">%title</span>',
            ));

            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;

        endwhile; // End of the loop.
        ?>
    </main>
</div>

<?php
get_sidebar();
get_footer();
