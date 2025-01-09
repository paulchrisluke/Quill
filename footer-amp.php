<?php

/**
 * The template for displaying the footer in AMP
 */
?>
</div><!-- .content-area -->

<footer class="site-footer">
    <div class="footer-widgets">
        <?php if (is_active_sidebar('footer-1')) : ?>
            <div class="footer-widget-area">
                <?php dynamic_sidebar('footer-1'); ?>
            </div>
        <?php endif; ?>
    </div>

    <nav class="footer-navigation">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'menu_class' => 'footer-menu',
            'container' => false,
            'depth' => 1,
            'walker' => new Quill_AMP_Nav_Walker()
        ));
        ?>
    </nav>

    <div class="site-info">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.
            <?php esc_html_e('Powered by', 'quill'); ?>
            <a href="<?php echo esc_url('https://wordpress.org/'); ?>">WordPress</a>
        </p>
    </div>
</footer>

<?php if (get_theme_mod('quill_adsense_footer_enabled', true)) : ?>
    <div class="ad-container ad-footer">
        <amp-ad
            type="adsense"
            layout="responsive"
            width="728"
            height="90"
            data-ad-client="<?php echo esc_attr(get_theme_mod('quill_adsense_client_id')); ?>"
            data-ad-slot="<?php echo esc_attr(get_theme_mod('quill_adsense_footer_slot')); ?>">
        </amp-ad>
    </div>
<?php endif; ?>
</body>

</html>