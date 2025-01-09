<?php

/**
 * AMP Integration Class
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

class Quill_AMP
{
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        add_action('wp', array($this, 'init'));
        add_filter('amp_content_sanitizers', array($this, 'add_sanitizers'));
    }

    /**
     * Initialize AMP functionality
     */
    public function init()
    {
        if (!is_amp()) {
            return;
        }

        // Remove standard scripts
        add_filter('wp_enqueue_scripts', array($this, 'dequeue_scripts'), 999);

        // Add AMP scripts
        add_action('wp_head', array($this, 'add_amp_scripts'), 1);
    }

    /**
     * Add AMP sanitizers
     */
    public function add_sanitizers($sanitizers)
    {
        require_once QUILL_PATH . '/includes/amp/class-quill-amp-sanitizer.php';
        $sanitizers['Quill_AMP_Sanitizer'] = array();
        return $sanitizers;
    }

    /**
     * Dequeue non-AMP scripts
     */
    public function dequeue_scripts()
    {
        global $wp_scripts;
        $wp_scripts->queue = array();
    }

    /**
     * Add required AMP scripts
     */
    public function add_amp_scripts()
    {
        if (!is_amp()) {
            return;
        }
?>
        <script async src="https://cdn.ampproject.org/v0.js"></script>
        <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
        <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
<?php
    }

    /**
     * Convert standard image to AMP image
     */
    public static function convert_image_to_amp($image_html)
    {
        if (!is_amp()) {
            return $image_html;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($image_html, 'HTML-ENTITIES', 'UTF-8'));
        $img = $dom->getElementsByTagName('img')->item(0);

        if (!$img) {
            return $image_html;
        }

        $amp_img = $dom->createElement('amp-img');
        foreach ($img->attributes as $attr) {
            $amp_img->setAttribute($attr->nodeName, $attr->nodeValue);
        }

        // Set layout attribute if not present
        if (!$amp_img->hasAttribute('layout')) {
            $amp_img->setAttribute('layout', 'responsive');
        }

        // Ensure width and height are set
        if (!$amp_img->hasAttribute('width')) {
            $amp_img->setAttribute('width', '800');
        }
        if (!$amp_img->hasAttribute('height')) {
            $amp_img->setAttribute('height', '600');
        }

        $img->parentNode->replaceChild($amp_img, $img);

        $html = $dom->saveHTML($amp_img);
        return $html;
    }
}

// Initialize AMP support
Quill_AMP::get_instance();
