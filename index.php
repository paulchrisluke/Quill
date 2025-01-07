<?php get_header(); ?>

<div class="site-container">
    <main id="primary" class="site-main">
        <?php if (have_posts()) : ?>
            <div class="recipe-feed">
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('recipe-card'); ?>>
                        <div class="recipe-card-content">
                            <header class="recipe-card-header">
                                <div class="recipe-meta">
                                    <?php
                                    $author_avatar = get_avatar_url(get_the_author_meta('ID'), array('size' => 32));
                                    if ($author_avatar) :
                                    ?>
                                        <amp-img
                                            class="author-avatar"
                                            src="<?php echo esc_url($author_avatar); ?>"
                                            width="32"
                                            height="32"
                                            alt="<?php echo esc_attr(get_the_author()); ?>">
                                        </amp-img>
                                    <?php endif; ?>
                                    <div class="author-meta">
                                        <span class="author-name"><?php the_author(); ?></span>
                                        <span class="meta-separator">•</span>
                                        <time class="posted-date" datetime="<?php echo get_the_date('c'); ?>">
                                            <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                                        </time>
                                    </div>
                                </div>
                                <button class="more-options">⋮</button>
                            </header>

                            <a href="<?php the_permalink(); ?>" class="recipe-card-link">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="recipe-card-media">
                                        <?php
                                        $thumb_id = get_post_thumbnail_id();
                                        $thumb_url = wp_get_attachment_image_src($thumb_id, 'large', true);
                                        $thumb_meta = wp_get_attachment_metadata($thumb_id);
                                        $width = $thumb_meta['width'] ?? 1200;
                                        $height = $thumb_meta['height'] ?? 800;
                                        ?>
                                        <amp-img
                                            src="<?php echo esc_url($thumb_url[0]); ?>"
                                            width="<?php echo esc_attr($width); ?>"
                                            height="<?php echo esc_attr($height); ?>"
                                            layout="responsive"
                                            alt="<?php echo esc_attr(get_the_title()); ?>">
                                        </amp-img>
                                    </div>
                                <?php endif; ?>
                            </a>

                            <div class="recipe-card-actions">
                                <div class="action-buttons">
                                    <button class="action-btn like" role="button" tabindex="0">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                    </button>
                                    <button class="action-btn comment" role="button" tabindex="0">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="action-btn share" role="button" tabindex="0">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 3L15 10M22 3L15 3M22 3L22 10"></path>
                                            <path d="M22 19V14C22 12.8954 21.1046 12 20 12H4C2.89543 12 2 12.8954 2 14V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19Z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <button class="action-btn save" role="button" tabindex="0">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="recipe-card-content-text">
                                <h2 class="recipe-title">
                                    <?php the_title(); ?>
                                </h2>

                                <div class="recipe-card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>

                                <?php
                                $prep_time = get_post_meta(get_the_ID(), '_recipe_prep_time', true);
                                $cook_time = get_post_meta(get_the_ID(), '_recipe_cook_time', true);
                                if ($prep_time || $cook_time) :
                                ?>
                                    <div class="recipe-times">
                                        <?php if ($prep_time) : ?>
                                            <span class="prep-time">
                                                <span>Prep: <?php echo esc_html($prep_time); ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($cook_time) : ?>
                                            <span class="cook-time">
                                                <span>Cook: <?php echo esc_html($cook_time); ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="recipe-categories">
                                    <?php
                                    $categories = get_the_category();
                                    foreach ($categories as $category) {
                                        printf(
                                            '<a href="%s" class="category-tag">#%s</a>',
                                            esc_url(get_category_link($category->term_id)),
                                            esc_html($category->name)
                                        );
                                    }
                                    ?>
                                </div>

                                <time class="posted-date" datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date(); ?>
                                </time>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php
            the_posts_pagination(array(
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'mid_size' => 1,
            ));
            ?>

        <?php else : ?>
            <p><?php esc_html_e('No recipes found.', 'quill'); ?></p>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>