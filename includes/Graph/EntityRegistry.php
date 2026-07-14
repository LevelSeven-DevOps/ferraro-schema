<?php

declare(strict_types=1);

namespace Ferraro\Schema\Graph;

/**
 * Class EntityRegistry
 * * Standardizes the generation of canonical global IDs (@id) used throughout the knowledge graph.
 */
final class EntityRegistry
{
    private string $siteUrl;

    public function __construct()
    {
        $this->siteUrl = trailingslashit(get_home_url());
    }

    /**
     * Generate a unique canonical ID for the primary Organization.
     *
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->siteUrl . '#organization';
    }

    /**
     * Generate a canonical ID for a specific post-type based entity.
     *
     * @param string $postType The custom post type (e.g. 'team', 'practice-area').
     * @param string $slug     The post slug (e.g. 'james-ferraro').
     * @param string $anchor   The unique anchor representation (e.g. 'person').
     * @return string
     */
    public function getEntityId(string $postType, string $slug, string $anchor): string
    {
        return sprintf('%s%s/%s/#%s', $this->siteUrl, $postType, $slug, $anchor);
    }
}