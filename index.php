<?php

/**
 * The main template file
 */

get_header('amp');
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
                    <?php
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