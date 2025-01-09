<?php

/**
 * The template for displaying the footer
 *
 * @package Quill
 */

if (! defined('ABSPATH')) {
    exit;
}
?>
</div><!-- .container -->
</div><!-- #content -->

<footer class="site-footer">
    <div class="container">
        <?php if (is_active_sidebar('footer-1')) : ?>
            <div class="footer-widgets">
                <?php dynamic_sidebar('footer-1'); ?>
            </div>
        <?php endif; ?>

        <?php if (has_nav_menu('footer')) : ?>
            <nav class="footer-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'menu_class' => 'footer-menu',
                    'container' => false,
                    'depth' => 1
                ));
                ?>
            </nav>
        <?php endif; ?>

        <?php if (get_theme_mod('quill_adsense_slot_footer') && get_theme_mod('quill_adsense_client')) : ?>
            <div class="ad-container ad-footer">
                <amp-ad
                    type="adsense"
                    layout="responsive"
                    width="728"
                    height="90"
                    data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client')); ?>"
                    data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_slot_footer')); ?>">
                </amp-ad>
            </div>
        <?php endif; ?>

        <div class="site-info">
            <p>&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>.
                <?php esc_html_e('Powered by', 'quill'); ?>
                <a href="<?php echo esc_url('https://wordpress.org/'); ?>">WordPress</a>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>