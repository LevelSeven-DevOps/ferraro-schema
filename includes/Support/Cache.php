<?php

declare(strict_types=1);

namespace Ferraro\Schema\Support;

/**
 * Class Cache
 * * WP-compliant transients caching mechanism to optimize schema building performance.
 */
final class Cache
{
    private const TRANSIENT_PREFIX = 'fsde_schema_';

    /**
     * Get a cached schema entry by key.
     *
     * @param string $key
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        $cached = get_transient(self::TRANSIENT_PREFIX . $key);
        
        return is_array($cached) ? $cached : null;
    }

    /**
     * Store schema output in cache.
     *
     * @param string $key
     * @param array<string, mixed> $data
     * @param int $expiration Expiration time in seconds. Defaults to 12 hours.
     * @return bool
     */
    public function set(string $key, array $data, int $expiration = 43200): bool
    {
        return set_transient(self::TRANSIENT_PREFIX . $key, $data, $expiration);
    }

    /**
     * Delete a cached schema entry.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return delete_transient(self::TRANSIENT_PREFIX . $key);
    }
}