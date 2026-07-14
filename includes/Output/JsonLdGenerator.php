<?php

declare(strict_types=1);

namespace Ferraro\Schema\Output;

/**
 * Class JsonLdGenerator
 * * Responsible for converting a validated Schema array into a secure JSON-LD script tag.
 */
final class JsonLdGenerator
{
    /**
     * Generate the JSON-LD script tag block.
     *
     * @param array<string, mixed> $data
     * @return string
     */
    public function generate(array $data): string
    {
        // Use JSON_UNESCAPED_SLASHES to keep URLs clean and JSON_UNESCAPED_UNICODE for proper text encoding
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return '';
        }

        return sprintf("<script type=\"application/ld+json\">\n%s\n</script>\n", $json);
    }
}