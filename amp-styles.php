<?php

/**
 * AMP Styles
 *
 * @package Quill
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function quill_get_amp_styles()
{
    return '
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .site-header {
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 1rem 0;
    }
    .site-title {
        margin: 0;
        font-size: 2rem;
    }
    .site-title a {
        color: #333;
        text-decoration: none;
    }
    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .nav-menu a {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    .recipe-post {
        max-width: 800px;
        margin: 0 auto;
    }
    .recipe-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .ad-container {
        background: #f5f5f5;
        margin: 2rem 0;
        text-align: center;
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    amp-img {
        max-width: 100%;
        height: auto;
    }
    amp-iframe {
        margin: 1rem 0;
    }
    @media screen and (max-width: 782px) {
        .site-header {
            padding: 0.5rem 0;
        }
        .site-title {
            font-size: 1.5rem;
        }
        .nav-menu {
            margin-top: 0.5rem;
        }
    }';
}

function quill_print_amp_styles()
{
    $styles = quill_get_amp_styles();
    if (!empty($styles)) {
        echo '<style amp-custom>' . trim($styles) . '</style>';
    }
}
