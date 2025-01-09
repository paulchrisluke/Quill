<!doctype html>
<html ⚡ lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <link rel="canonical" href="<?php echo esc_url(get_permalink()); ?>">
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
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
    <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
    <script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>
    <style amp-custom>
        :root {
            --primary-color: #333;
            --secondary-color: #666;
            --accent-color: #4CAF50;
            --background-color: #fff;
            --text-color: #333;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
        }

        .site-header {
            background: var(--primary-color);
            padding: 1rem;
            color: white;
        }

        .site-title {
            margin: 0;
            font-size: 1.5rem;
        }

        .site-title a {
            color: white;
            text-decoration: none;
        }

        .hamburger {
            position: fixed;
            top: 1rem;
            left: 1rem;
            padding: 0.5rem;
            background: var(--primary-color);
            border: none;
            color: white;
            cursor: pointer;
            z-index: 100;
        }

        .content-area {
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .ad-container {
            background: #f0f0f0;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
        }

        amp-sidebar {
            background: var(--primary-color);
            color: white;
            width: 250px;
            padding: 1rem;
        }

        .menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu-item {
            margin: 0.5rem 0;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 0.5rem;
        }

        amp-iframe {
            margin: 1rem 0;
        }
    </style>
    <title><?php wp_title('|', true, 'right'); ?></title>
</head>

<body>
    <button on="tap:sidebar.toggle" class="hamburger">☰</button>

    <amp-sidebar id="sidebar" layout="nodisplay" side="left">
        <?php if (has_nav_menu('primary')) {
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'menu',
                'walker' => new Quill_AMP_Nav_Walker()
            ));
        } ?>
    </amp-sidebar>

    <header class="site-header">
        <h1 class="site-title">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>
        </h1>
    </header>

    <div class="content-area">