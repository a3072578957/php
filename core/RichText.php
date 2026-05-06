<?php

namespace Core;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'u', 'h2', 'h3', 'blockquote', 'ul', 'ol', 'li', 'a', 'img', 'code', 'pre', 'hr'
    ];

    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt'],
    ];

    public static function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapper = '<div>' . $html . '</div>';
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root instanceof DOMElement) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return '';
        }

        self::sanitizeNode($root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return trim($output);
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMComment) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
            return;
        }

        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                self::unwrap($node);
                return;
            }

            self::sanitizeAttributes($node, $tag);
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            self::sanitizeNode($child);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
        $attributes = [];

        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute->name;
        }

        foreach ($attributes as $name) {
            if (!in_array(strtolower($name), $allowed, true)) {
                $element->removeAttribute($name);
            }
        }

        if ($tag === 'a') {
            $href = trim((string) $element->getAttribute('href'));
            if (!self::isSafeUrl($href, false)) {
                $element->removeAttribute('href');
            }

            $target = trim((string) $element->getAttribute('target'));
            if ($target === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
                $element->removeAttribute('rel');
            }
        }

        if ($tag === 'img') {
            $src = trim((string) $element->getAttribute('src'));
            if (!self::isSafeUrl($src, true)) {
                if ($element->parentNode) {
                    $element->parentNode->removeChild($element);
                }
                return;
            }
        }
    }

    private static function isSafeUrl(string $url, bool $imageMode): bool
    {
        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/uploads/') || str_starts_with($url, '/')) {
            return true;
        }

        if (!$imageMode && str_starts_with($url, '#')) {
            return true;
        }

        if (!$imageMode && str_starts_with($url, 'mailto:')) {
            return true;
        }

        return (bool) preg_match('#^https?://#i', $url);
    }

    private static function unwrap(DOMNode $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }
}
