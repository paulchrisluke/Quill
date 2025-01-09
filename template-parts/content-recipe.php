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

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php
        if (has_post_thumbnail()) :
            $thumbnail_id = get_post_thumbnail_id();
            $media_details = quill_get_media_details($thumbnail_id);
            if ($media_details) :
        ?>
                <amp-img
                    src="<?php echo esc_url($media_details['url']); ?>"
                    width="<?php echo esc_attr($media_details['width']); ?>"
                    height="<?php echo esc_attr($media_details['height']); ?>"
                    layout="responsive"
                    alt="<?php echo esc_attr($media_details['alt']); ?>"></amp-img>
        <?php
            endif;
        endif;
        ?>

        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

        <div class="entry-meta">
            <?php
            // Add recipe meta information here
            echo '<span class="posted-on">' . esc_html(get_the_date()) . '</span>';
            ?>
        </div>
    </header>

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
        $categories_list = get_the_category_list(esc_html__(', ', 'quill'));
        if ($categories_list) {
            printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'quill') . '</span>', $categories_list);
        }

        $tags_list = get_the_tag_list('', esc_html__(', ', 'quill'));
        if ($tags_list) {
            printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'quill') . '</span>', $tags_list);
        }
        ?>
    </footer>
</article>