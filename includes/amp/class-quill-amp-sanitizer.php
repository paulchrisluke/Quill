<?php

/**
 * AMP Content Sanitizer
 *
 * @package Quill
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Only proceed if AMP plugin is active
if (!class_exists('AMP_Base_Sanitizer')) {
    return;
}

use AMP_Base_Sanitizer;

class Quill_AMP_Sanitizer extends AMP_Base_Sanitizer
{
    /**
     * Tag names that should be converted to AMP versions
     */
    private $tags_to_convert = array(
        'img' => 'amp-img',
        'iframe' => 'amp-iframe',
        'video' => 'amp-video',
        'audio' => 'amp-audio',
        'form' => 'form',
    );

    /**
     * Attributes that should be removed
     */
    private $attributes_to_remove = array(
        'onclick',
        'onload',
        'onunload',
        'onchange',
        'onsubmit',
        'onreset',
        'style',
        'javascript:',
    );

    /**
     * Sanitize the content
     */
    public function sanitize()
    {
        $body = $this->get_body_node();
        $this->sanitize_node($body);
        $this->ensure_required_markup();
    }

    /**
     * Ensure required AMP markup is present
     */
    private function ensure_required_markup()
    {
        $head = $this->dom->getElementsByTagName('head')->item(0);

        if (!$head) {
            return;
        }

        // Add required meta viewport
        if (!$this->has_meta_viewport()) {
            $meta_viewport = $this->dom->createElement('meta');
            $meta_viewport->setAttribute('name', 'viewport');
            $meta_viewport->setAttribute('content', 'width=device-width,minimum-scale=1,initial-scale=1');
            $head->appendChild($meta_viewport);
        }

        // Add canonical link if missing
        if (!$this->has_canonical_link()) {
            $canonical = $this->dom->createElement('link');
            $canonical->setAttribute('rel', 'canonical');
            $canonical->setAttribute('href', esc_url(get_permalink()));
            $head->appendChild($canonical);
        }

        // Add required AMP scripts
        $this->ensure_amp_scripts($head);
    }

    /**
     * Check if meta viewport exists
     */
    private function has_meta_viewport()
    {
        $meta_tags = $this->dom->getElementsByTagName('meta');
        foreach ($meta_tags as $tag) {
            if ($tag->getAttribute('name') === 'viewport') {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if canonical link exists
     */
    private function has_canonical_link()
    {
        $link_tags = $this->dom->getElementsByTagName('link');
        foreach ($link_tags as $tag) {
            if ($tag->getAttribute('rel') === 'canonical') {
                return true;
            }
        }
        return false;
    }

    /**
     * Ensure required AMP scripts are present
     */
    private function ensure_amp_scripts($head)
    {
        $required_scripts = array(
            array(
                'src' => 'https://cdn.ampproject.org/v0.js',
                'async' => true,
            ),
            array(
                'src' => 'https://cdn.ampproject.org/v0/amp-form-0.1.js',
                'async' => true,
                'custom-element' => 'amp-form',
            ),
            array(
                'src' => 'https://cdn.ampproject.org/v0/amp-sidebar-0.1.js',
                'async' => true,
                'custom-element' => 'amp-sidebar',
            ),
            array(
                'src' => 'https://cdn.ampproject.org/v0/amp-carousel-0.1.js',
                'async' => true,
                'custom-element' => 'amp-carousel',
            ),
            array(
                'src' => 'https://cdn.ampproject.org/v0/amp-image-lightbox-0.1.js',
                'async' => true,
                'custom-element' => 'amp-image-lightbox',
            ),
            array(
                'src' => 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js',
                'async' => true,
                'custom-element' => 'amp-analytics',
            ),
            array(
                'src' => 'https://cdn.ampproject.org/v0/amp-ad-0.1.js',
                'async' => true,
                'custom-element' => 'amp-ad',
            ),
        );

        foreach ($required_scripts as $script_data) {
            if (!$this->has_script($script_data['src'])) {
                $script = $this->dom->createElement('script');
                foreach ($script_data as $attr => $value) {
                    $script->setAttribute($attr, $value);
                }
                $head->appendChild($script);
            }
        }
    }

    /**
     * Check if script exists
     */
    private function has_script($src)
    {
        $scripts = $this->dom->getElementsByTagName('script');
        foreach ($scripts as $script) {
            if ($script->getAttribute('src') === $src) {
                return true;
            }
        }
        return false;
    }

    /**
     * Sanitize a DOM node
     */
    private function sanitize_node($node)
    {
        if (!$node) {
            return;
        }

        // If node is an element node
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag_name = strtolower($node->tagName);

            // Convert to AMP version if needed
            if (isset($this->tags_to_convert[$tag_name])) {
                $this->convert_to_amp_tag($node, $this->tags_to_convert[$tag_name]);
            }

            // Remove disallowed attributes
            foreach ($this->attributes_to_remove as $attr) {
                if ($node->hasAttribute($attr)) {
                    $node->removeAttribute($attr);
                }
            }

            // Force HTTPS on all URLs
            $this->force_https_urls($node);

            // Handle specific tags
            switch ($tag_name) {
                case 'img':
                case 'amp-img':
                    $this->sanitize_image($node);
                    break;
                case 'iframe':
                case 'amp-iframe':
                    $this->sanitize_iframe($node);
                    break;
                case 'form':
                    $this->sanitize_form($node);
                    break;
                case 'a':
                    $this->sanitize_link($node);
                    break;
            }
        }

        // Recursively sanitize child nodes
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child_node) {
                $this->sanitize_node($child_node);
            }
        }
    }

    /**
     * Convert a regular tag to its AMP equivalent
     */
    private function convert_to_amp_tag($node, $amp_tag_name)
    {
        // Don't convert if it's already an AMP tag
        if (strpos($node->tagName, 'amp-') === 0) {
            return;
        }

        $amp_node = $this->dom->createElement($amp_tag_name);

        // Copy all attributes
        foreach ($node->attributes as $attribute) {
            $amp_node->setAttribute($attribute->nodeName, $attribute->nodeValue);
        }

        // Copy all child nodes
        while ($node->firstChild) {
            $amp_node->appendChild($node->firstChild);
        }

        // Replace original node with AMP version
        $node->parentNode->replaceChild($amp_node, $node);
    }

    /**
     * Force HTTPS on all URLs
     */
    private function force_https_urls($node)
    {
        $url_attributes = array('src', 'href', 'action');

        foreach ($url_attributes as $attr) {
            if ($node->hasAttribute($attr)) {
                $url = $node->getAttribute($attr);
                if (strpos($url, 'http://') === 0) {
                    $node->setAttribute($attr, str_replace('http://', 'https://', $url));
                }
            }
        }
    }

    /**
     * Sanitize image tags
     */
    private function sanitize_image($node)
    {
        // Ensure required attributes
        if (!$node->hasAttribute('width') || !$node->hasAttribute('height')) {
            $src = $node->getAttribute('src');
            $size = @getimagesize($src);
            if ($size) {
                $node->setAttribute('width', $size[0]);
                $node->setAttribute('height', $size[1]);
            } else {
                $node->setAttribute('width', '800');
                $node->setAttribute('height', '600');
            }
        }

        if (!$node->hasAttribute('layout')) {
            $node->setAttribute('layout', 'responsive');
        }
    }

    /**
     * Sanitize iframe tags
     */
    private function sanitize_iframe($node)
    {
        // Ensure required attributes
        if (!$node->hasAttribute('width')) {
            $node->setAttribute('width', '800');
        }
        if (!$node->hasAttribute('height')) {
            $node->setAttribute('height', '600');
        }
        if (!$node->hasAttribute('layout')) {
            $node->setAttribute('layout', 'responsive');
        }
        if (!$node->hasAttribute('sandbox')) {
            $node->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-popups allow-forms');
        }
        if (!$node->hasAttribute('frameborder')) {
            $node->setAttribute('frameborder', '0');
        }
    }

    /**
     * Sanitize form tags
     */
    private function sanitize_form($node)
    {
        // Ensure form has proper AMP attributes
        if (!$node->hasAttribute('action-xhr')) {
            $action = $node->getAttribute('action');
            if ($action) {
                $node->setAttribute('action-xhr', $action);
                $node->removeAttribute('action');
            }
        }

        if (!$node->hasAttribute('target')) {
            $node->setAttribute('target', '_top');
        }

        // Add required AMP form elements
        $this->add_form_elements($node);
    }

    /**
     * Add required AMP form elements
     */
    private function add_form_elements($form_node)
    {
        // Add submit-success element if not present
        if (!$this->has_child_with_attribute($form_node, 'submit-success')) {
            $success = $this->dom->createElement('div');
            $success->setAttribute('submit-success', '');
            $success->appendChild($this->dom->createElement('template', 'Form submitted successfully!'));
            $form_node->appendChild($success);
        }

        // Add submit-error element if not present
        if (!$this->has_child_with_attribute($form_node, 'submit-error')) {
            $error = $this->dom->createElement('div');
            $error->setAttribute('submit-error', '');
            $error->appendChild($this->dom->createElement('template', 'Error submitting form.'));
            $form_node->appendChild($error);
        }
    }

    /**
     * Check if node has child with specific attribute
     */
    private function has_child_with_attribute($node, $attribute)
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->hasAttribute($attribute)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Sanitize link tags
     */
    private function sanitize_link($node)
    {
        // Ensure links have proper target attribute
        if (!$node->hasAttribute('target')) {
            $node->setAttribute('target', '_blank');
        }
        if ($node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }
}
