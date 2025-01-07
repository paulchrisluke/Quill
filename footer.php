        </div><!-- .container -->
        </div><!-- .site-content -->

        <footer class="site-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-widgets">
                        <?php if (is_active_sidebar('footer-1')): ?>
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
                        ));
                        ?>
                    </nav>

                    <div class="site-info">
                        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>

        <?php wp_footer(); ?>
        </body>

        </html>