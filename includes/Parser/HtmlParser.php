<?php

declare(strict_types=1);

namespace Ferraro\Schema\Parser;

/**
 * Class HtmlParser
 * * Responsible for parsing WYSIWYG HTML blocks into clean structured arrays or plain strings.
 */
final class HtmlParser
{
    /**
     * Extracts bullet points/list items from a WYSIWYG HTML string.
     * Safe against non-string parameters (e.g., boolean false returned by empty ACF fields).
     *
     * @param mixed $html
     * @return array<string>
     */
    public function extractListItems(mixed $html): array
    {
        if (empty($html) || !is_string($html)) {
            return [];
        }

        $items = [];

        // Try extracting using DOMDocument for reliability
        if (class_exists('DOMDocument')) {
            $dom = new \DOMDocument();
            // Prevent warnings on modern HTML5 elements
            libxml_use_internal_errors(true);
            
            // Load HTML with UTF-8 encoding
            $loaded = @$dom->loadHTML(
                '<?xml encoding="utf-8" ?>' . $html, 
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            if ($loaded) {
                $lis = $dom->getElementsByTagName('li');
                if ($lis->length > 0) {
                    foreach ($lis as $li) {
                        $text = trim($li->textContent);
                        if ($text !== '') {
                            $items[] = $text;
                        }
                    }
                    libxml_clear_errors();
                    return $items;
                }
            }
            libxml_clear_errors();
        }

        // Fallback: If no LI tags are found, split by paragraphs/breaks and clean up
        $cleanHtml = strip_tags($html, '<p><br>');
        $lines = preg_split('/<p>|<br\s*\/?>|<\/p>/i', $cleanHtml);

        if ($lines !== false) {
            foreach ($lines as $line) {
                $text = trim(html_entity_decode(strip_tags($line)));
                if ($text !== '') {
                    $items[] = $text;
                }
            }
        }

        return $items;
    }

    /**
     * Cleans a raw WYSIWYG block to a plain text string suitable for description fields.
     * Safe against non-string parameters (e.g., boolean false returned by empty ACF fields).
     *
     * @param mixed $html
     * @return string|null
     */
    public function toPlainText(mixed $html): ?string
    {
        if (empty($html) || !is_string($html)) {
            return null;
        }

        $cleaned = strip_tags($html);
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return $cleaned ? trim($cleaned) : null;
    }
}