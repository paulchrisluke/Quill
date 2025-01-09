<!doctype html>
<html ⚡ <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <?php $canonical_url = is_front_page() ? home_url('/') : get_permalink(); ?>
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">

    <style amp-boilerplate>
        body {
            -webkit-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            -moz-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            -ms-animation: -amp-start 8s steps(1, end) 0s 1 normal both;
            animation: -amp-start 8s steps(1, end) 0s 1 normal both
        }

        @-webkit-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-moz-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-ms-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @-o-keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }

        @keyframes -amp-start {
            from {
                visibility: hidden
            }

            to {
                visibility: visible
            }
        }
    </style>
    <noscript>
        <style amp-boilerplate>
            body {
                -webkit-animation: none;
                -moz-animation: none;
                -ms-animation: none;
                animation: none
            }
        </style>
    </noscript>

    <!-- AMP Components -->
    <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
    <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>

    <style amp-custom>
        /* Base styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .site-header {
            background: #fff;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .site-branding {
            margin-bottom: 1rem;
            text-align: center;
        }

        .site-title {
            margin: 0;
            font-size: 2rem;
        }

        .site-title a {
            color: #333;
            text-decoration: none;
        }

        .site-description {
            margin: 0.5rem 0 0;
            color: #666;
        }

        /* Navigation */
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-menu li {
            margin: 0 10px;
        }

        .nav-menu a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 5px 0;
        }

        .nav-menu a:hover {
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
        }

        /* Accessibility */
        .screen-reader-text {
            border: 0;
            clip: rect(1px, 1px, 1px, 1px);
            clip-path: inset(50%);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
            word-wrap: normal;
        }

        .screen-reader-text:focus {
            background-color: #f1f1f1;
            border-radius: 3px;
            box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
            clip: auto;
            clip-path: none;
            color: #21759b;
            display: block;
            font-size: 14px;
            font-weight: bold;
            height: auto;
            left: 5px;
            line-height: normal;
            padding: 15px 23px 14px;
            text-decoration: none;
            top: 5px;
            width: auto;
            z-index: 100000;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .nav-menu {
                flex-direction: column;
                align-items: center;
            }

            .nav-menu li {
                margin: 5px 0;
            }
        }

        /* AMP Ad Container */
        .amp-ad-container {
            background: #f8f8f8;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
        }
    </style>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <a class="skip-link screen-reader-text" href="#content">
        <?php esc_html_e('Skip to content', 'quill'); ?>
    </a>

    <header class="site-header">
        <div class="container">
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php endif; ?>

                <?php if (display_header_text()) : ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <?php bloginfo('name'); ?>
                        </a>
                    </h1>

                    <?php
                    $description = get_bloginfo('description', 'display');
                    if ($description) : ?>
                        <p class="site-description"><?php echo $description; ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <nav class="site-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'nav-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>
        </div>

        <?php if (function_exists('quill_adsense_header')) : ?>
            <div class="amp-ad-container">
                <?php quill_adsense_header(); ?>
            </div>
        <?php endif; ?>
    </header>

    <div id="content" class="site-content">
        <div class="container">