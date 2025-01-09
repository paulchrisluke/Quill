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
        <nav class="footer-navigation">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer',
                'menu_class'     => 'nav-menu',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 1,
                'walker'         => new Quill_AMP_Nav_Walker(),
            ));
            ?>
        </nav>

        <div class="site-info">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.
                <?php
                printf(
                    /* translators: %s: WordPress. */
                    esc_html__('Proudly powered by %s', 'quill'),
                    '<a href="https://wordpress.org/">WordPress</a>'
                );
                ?>
            </p>
        </div>
    </div>
</footer>
</body>

</html>