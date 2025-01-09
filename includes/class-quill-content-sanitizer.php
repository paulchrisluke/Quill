<?php

/**
 * Content Sanitizer for AMP Compliance
 *
 * @package Quill
 */

if (!defined('ABSPATH')) {
    exit;
}

class Quill_Content_Sanitizer
{
    /**
     * DOM Document instance
     */
    private $dom;

    /**
     * Tags that need to be converted to AMP versions
     */
    private $amp_tags = array(
        'img' => 'amp-img',
        'iframe' => 'amp-iframe',
        'video' => 'amp-video',
        'audio' => 'amp-audio'
    );

    /**
     * Attributes that should be removed
     */
    private $disallowed_attributes = array(
        'onclick',
        'onload',
        'onunload',
        'onchange',
        'onsubmit',
        'onreset',
        'style',
        'javascript:'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        add_filter('the_content', array($this, 'sanitize_content'), 999);
        add_filter('widget_text_content', array($this, 'sanitize_content'), 999);
    }

    /**
     * Sanitize content for AMP compliance
     */
    public function sanitize_content($content)
    {
        if (is_admin()) {
            return $content;
        }

        // Create new DOMDocument
        $this->dom = new DOMDocument();

        // Preserve UTF-8 encoding
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        // Load HTML, suppressing warnings
        @$this->dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Convert tags to AMP versions
        $this->convert_tags();

        // Remove disallowed attributes
        $this->remove_disallowed_attributes();

        // Force HTTPS on all URLs
        $this->force_https_urls();

        // Get sanitized content
        $content = $this->dom->saveHTML();

        // Clean up the output
        $content = $this->clean_output($content);

        return $content;
    }

    /**
     * Convert regular tags to AMP versions
     */
    private function convert_tags()
    {
        foreach ($this->amp_tags as $old_tag => $new_tag) {
            $elements = $this->dom->getElementsByTagName($old_tag);

            // Convert elements from the bottom up to avoid issues with the NodeList being live
            for ($i = $elements->length - 1; $i >= 0; $i--) {
                $element = $elements->item($i);
                $this->convert_to_amp_tag($element, $new_tag);
            }
        }
    }

    /**
     * Convert a single tag to its AMP version
     */
    private function convert_to_amp_tag($element, $new_tag_name)
    {
        // Create new element
        $new_element = $this->dom->createElement($new_tag_name);

        // Copy attributes
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attribute) {
                $new_element->setAttribute($attribute->name, $attribute->value);
            }
        }

        // Special handling for images
        if ($new_tag_name === 'amp-img') {
            $this->handle_amp_image($new_element);
        }

        // Special handling for iframes
        if ($new_tag_name === 'amp-iframe') {
            $this->handle_amp_iframe($new_element);
        }

        // Copy child nodes
        while ($element->firstChild) {
            $new_element->appendChild($element->firstChild);
        }

        // Replace old element with new one
        $element->parentNode->replaceChild($new_element, $element);
    }

    /**
     * Remove disallowed attributes from all elements
     */
    private function remove_disallowed_attributes()
    {
        $xpath = new DOMXPath($this->dom);

        // Get all elements
        $elements = $xpath->query('//*');

        if ($elements) {
            foreach ($elements as $element) {
                if ($element->hasAttributes()) {
                    foreach ($this->disallowed_attributes as $attr) {
                        if ($element->hasAttribute($attr)) {
                            $element->removeAttribute($attr);
                        }
                    }
                }
            }
        }
    }

    /**
     * Force HTTPS on all URLs
     */
    private function force_https_urls()
    {
        $xpath = new DOMXPath($this->dom);
        $url_attributes = array('src', 'href', 'action');

        foreach ($url_attributes as $attr) {
            $elements = $xpath->query("//*[@{$attr}]");
            if ($elements) {
                foreach ($elements as $element) {
                    $url = $element->getAttribute($attr);
                    if (strpos($url, 'http://') === 0) {
                        $element->setAttribute($attr, str_replace('http://', 'https://', $url));
                    }
                }
            }
        }
    }

    /**
     * Handle AMP image specific requirements
     */
    private function handle_amp_image($element)
    {
        // Ensure layout attribute
        if (!$element->hasAttribute('layout')) {
            $element->setAttribute('layout', 'responsive');
        }

        // Ensure width and height
        if (!$element->hasAttribute('width') || !$element->hasAttribute('height')) {
            $src = $element->getAttribute('src');
            $size = @getimagesize($src);
            if ($size) {
                $element->setAttribute('width', $size[0]);
                $element->setAttribute('height', $size[1]);
            } else {
                $element->setAttribute('width', '800');
                $element->setAttribute('height', '600');
            }
        }
    }

    /**
     * Handle AMP iframe specific requirements
     */
    private function handle_amp_iframe($element)
    {
        // Ensure required attributes
        if (!$element->hasAttribute('width')) {
            $element->setAttribute('width', '800');
        }
        if (!$element->hasAttribute('height')) {
            $element->setAttribute('height', '600');
        }
        if (!$element->hasAttribute('layout')) {
            $element->setAttribute('layout', 'responsive');
        }
        if (!$element->hasAttribute('sandbox')) {
            $element->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-popups allow-forms');
        }
        if (!$element->hasAttribute('frameborder')) {
            $element->setAttribute('frameborder', '0');
        }
    }

    /**
     * Clean up the output HTML
     */
    private function clean_output($content)
    {
        // Remove <!DOCTYPE> and <html> tags
        $content = preg_replace('~<!DOCTYPE.*?>~i', '', $content);
        $content = preg_replace('~</?html.*?>~i', '', $content);
        $content = preg_replace('~</?body.*?>~i', '', $content);

        // Fix encoding issues
        $content = mb_convert_encoding($content, 'UTF-8', 'HTML-ENTITIES');

        return trim($content);
    }
}
