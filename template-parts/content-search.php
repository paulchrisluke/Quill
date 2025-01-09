<?php

/**
 * Template part for displaying results in search pages
 *
 * @package Quill
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php if (has_post_thumbnail()) : ?>
        <div class="entry-thumbnail">
            <a href="<?php the_permalink(); ?>">
                <?php
                $image_data = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium');
                if ($image_data) {
                    echo '<amp-img src="' . esc_url($image_data[0]) . '"
                        width="' . esc_attr($image_data[1]) . '"
                        height="' . esc_attr($image_data[2]) . '"
                        layout="responsive"
                        alt="' . esc_attr(get_the_title()) . '"></amp-img>';
                }
                ?>
            </a>
        </div>
    <?php endif; ?>

    <header class="entry-header">
        <?php the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>

        <?php if ('post' === get_post_type()) : ?>
            <div class="entry-meta">
                <?php
                printf(
                    '<span class="posted-on">%1$s</span>',
                    sprintf(
                        /* translators: %s: post date */
                        esc_html_x('Posted on %s', 'post date', 'quill'),
                        '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . esc_html(get_the_date()) . '</a>'
                    )
                );

                printf(
                    '<span class="byline"> %1$s</span>',
                    sprintf(
                        /* translators: %s: post author */
                        esc_html_x('by %s', 'post author', 'quill'),
                        '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
                    )
                );
                ?>
            </div>
        <?php endif; ?>
    </header>

    <div class="entry-summary">
        <?php the_excerpt(); ?>
    </div>

    <footer class="entry-footer">
        <?php
        $categories_list = get_the_category_list(esc_html__(', ', 'quill'));
        if ($categories_list) {
            printf(
                '<span class="cat-links">%1$s %2$s</span>',
                esc_html__('Posted in:', 'quill'),
                $categories_list
            );
        }

        $tags_list = get_the_tag_list('', esc_html__(', ', 'quill'));
        if ($tags_list) {
            printf(
                '<span class="tags-links">%1$s %2$s</span>',
                esc_html__('Tagged:', 'quill'),
                $tags_list
            );
        }
        ?>
    </footer>
</article>