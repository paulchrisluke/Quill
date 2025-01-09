<?php

/**
 * The main template file
 *
 * @package Quill
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
    ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php
                    if (is_singular()) :
                        the_title('<h1 class="entry-title">', '</h1>');
                    else :
                        the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
                    endif;
                    ?>
                </header>

                <div class="entry-content">
                    <?php
                    if (has_post_thumbnail()) :
                        $thumbnail_id = get_post_thumbnail_id();
                        error_log('Quill Theme - Thumbnail ID: ' . $thumbnail_id);
                        $media_details = quill_get_media_details($thumbnail_id);
                        if ($media_details) :
                    ?>
                            <amp-img
                                src="<?php echo esc_url($media_details['url']); ?>"
                                width="<?php echo absint($media_details['width']); ?>"
                                height="<?php echo absint($media_details['height']); ?>"
                                layout="responsive"
                                alt="<?php echo esc_attr($media_details['alt']); ?>">
                            </amp-img>
                    <?php
                        else:
                            error_log('Quill Theme - No media details returned for thumbnail ID: ' . $thumbnail_id);
                        endif;
                    endif;

                    the_content();
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
        endwhile;

        the_posts_navigation();
    else :
        ?>
        <p><?php esc_html_e('No posts found.', 'quill'); ?></p>
    <?php
    endif;
    ?>
</main>

<?php
get_footer('amp');
?>