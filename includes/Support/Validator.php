<?php

declare(strict_types=1);

namespace Ferraro\Schema\Support;

/**
 * Class Validator
 * * Validates, cleans, and prunes Schema arrays recursively to guarantee strict search console compliance.
 */
final class Validator
{
    /**
     * Clean schema arrays by recursively removing null values, empty strings, and empty arrays.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function validateAndClean(array $data): array
    {
        return $this->recursiveClean($data);
    }

    /**
     * Standard recursive cleaner logic.
     *
     * @param array<mixed> $array
     * @return array<mixed>
     */
    private function recursiveClean(array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->recursiveClean($value);
            }

            if ($this->isEmpty($value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Determine if a schema value is empty and should be stripped.
     *
     * @param mixed $value
     * @return bool
     */
    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }
}