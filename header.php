<?php

/**
 * The header for our theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> amp>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
    <?php wp_head(); ?>
    <style amp-custom>
        /* Layout */
        @media (min-width: 1024px) {
            body {
                display: grid;
                grid-template-columns: 280px 1fr;
                grid-template-areas: "header header" "sidebar main";
            }

            /* Admin Bar Adjustments */
            body.admin-bar .site-header {
                top: 32px;
            }

            body.admin-bar .desktop-sidebar {
                top: 92px;
                /* 60px header + 32px admin bar */
                height: calc(100vh - 92px);
            }

            .site-header {
                grid-area: header;
            }

            .desktop-sidebar {
                grid-area: sidebar;
                display: block;
                width: 280px;
                background: #fff;
                border-right: 1px solid #e4e6eb;
                height: calc(100vh - 60px);
                position: fixed;
                top: 60px;
                overflow-y: auto;
            }

            .site-content {
                grid-area: main;
                margin-left: 280px;
                padding: 24px;
                max-width: 800px;
            }

            amp-sidebar {
                display: none;
            }

            .menu-button {
                display: none;
            }
        }

        @media (max-width: 1023px) {
            .desktop-sidebar {
                display: none;
            }

            .site-content {
                padding: 16px;
                margin-top: 60px;
            }

            /* Admin Bar Adjustments for Mobile */
            body.admin-bar .site-header {
                top: 46px;
            }

            body.admin-bar .site-content {
                margin-top: 106px;
                /* 60px header + 46px admin bar */
            }
        }

        /* Header */
        .site-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #fff;
            display: flex;
            align-items: center;
            padding: 0 16px;
            z-index: 100;
        }

        .menu-button {
            border: none;
            background: none;
            font-size: 24px;
            padding: 8px;
            cursor: pointer;
        }

        .site-branding {
            margin-left: 16px;
        }

        .site-branding a {
            color: #262626;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            height: 32px;
            /* Match icon height */
        }

        .site-icon {
            border-radius: 8px;
            display: block;
            /* Ensure image is displayed */
        }

        /* Sidebar Styles (shared between mobile and desktop) */
        .sidebar-nav {
            padding: 16px;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 16px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            color: #262626;
            text-decoration: none;
            padding: 8px;
        }

        .category-letter {
            width: 32px;
            height: 32px;
            background: #e4e6eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-weight: bold;
            color: #262626;
            text-transform: uppercase;
            font-size: 14px;
        }

        /* Mobile Sidebar */
        amp-sidebar {
            background: #fff;
            width: 280px;
        }

        .sidebar-header {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 16px;
            border-bottom: 1px solid #e4e6eb;
        }

        .close-button {
            border: none;
            background: none;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
        }

        /* Main Content */
        .recipe-feed {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .recipe-card {
            background: #fff;
            border: 1px solid #e4e6eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .recipe-card-header {
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .recipe-meta {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .author-meta {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.9em;
        }

        .author-name {
            font-weight: 600;
        }

        .posted-date {
            color: #8e8e8e;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Header -->
    <header class="site-header">
        <button on="tap:sidebar.toggle" class="menu-button" aria-label="Menu">☰</button>
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php
                $site_icon_url = get_site_icon_url(32);
                if ($site_icon_url): ?>
                    <amp-img src="<?php echo esc_url($site_icon_url); ?>"
                        width="32"
                        height="32"
                        alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                        class="site-icon"
                        fallback="🍽️">
                    </amp-img>
                <?php else: ?>
                    <span class="site-icon-fallback">🍽️</span>
                    <?php bloginfo('name'); ?>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <!-- Desktop Sidebar -->
    <div class="desktop-sidebar">
        <nav class="sidebar-nav">
            <?php get_template_part('template-parts/sidebar-content'); ?>
        </nav>
    </div>

    <!-- Mobile Sidebar -->
    <amp-sidebar id="sidebar" layout="nodisplay" side="left">
        <div class="sidebar-header">
            <button on="tap:sidebar.close" class="close-button">✕</button>
        </div>
        <nav class="sidebar-nav">
            <?php get_template_part('template-parts/sidebar-content'); ?>
        </nav>
    </amp-sidebar>

    <!-- Main Content -->
    <main class="site-content">