<?php

declare(strict_types=1);

namespace Ferraro\Schema\Builders;

use Ferraro\Schema\Contracts\BuilderInterface;
use Ferraro\Schema\Contracts\EntityInterface;
use Ferraro\Schema\Entities\OrganizationEntity;
use Ferraro\Schema\Entities\PostalAddressEntity;
use Ferraro\Schema\Entities\PracticeAreaEntity;
use Ferraro\Schema\Entities\WebPageEntity;
use Ferraro\Schema\Graph\EntityRegistry;
use Ferraro\Schema\Parser\HtmlParser;

/**
 * Class PracticeAreaBuilder
 * * Builds the flat @graph schema for Practice Area pages.
 */
final class PracticeAreaBuilder implements BuilderInterface
{
    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly HtmlParser $parser
    ) {
    }

    public function supports(int $postId): bool
    {
        return get_post_type($postId) === 'practice-area'; // Adjust slug if needed
    }

    public function build(int $postId): array|EntityInterface|null
    {
        $post = get_post($postId);
        if (!$post) {
            return null;
        }

        $entities = [];

        // 1. Canonical URLs and IDs
        $canonicalUrl = rtrim(get_permalink($postId), '/');
        $serviceId = $canonicalUrl . '/#service';
        $webPageId = $canonicalUrl . '/#webpage';
        
        $orgId = $this->registry->getOrganizationId();
        $addressId = $orgId . 'postaladdress';

        // 2. WebPage Node
        $entities[] = new WebPageEntity(
            id: $webPageId,
            url: $canonicalUrl,
            name: trim(get_the_title($postId)) . ' | ' . get_bloginfo('name'),
            mainEntityId: $serviceId
        );

        // 3. PostalAddress Node
        $entities[] = new PostalAddressEntity(
            id: $addressId,
            streetAddress: '600 Brickell Ave Unit 3800',
            postalCode: '33131',
            addressLocality: 'Miami',
            addressRegion: 'FL',
            addressCountry: 'US'
        );

        // 4. Organization Node (Provider)
        $entities[] = new OrganizationEntity(
            id: $orgId,
            name: 'Ferraro Law Firm',
            url: home_url('/'),
            addressId: $addressId
        );

        // 5. Core Practice Area (Service) Node
        $description = get_field('practice_area_intro', $postId) ?: $post->post_excerpt;
        
        $entities[] = new PracticeAreaEntity(
            id: $serviceId,
            name: trim(get_the_title($postId)),
            url: $canonicalUrl,
            description: trim($this->parser->toPlainText($description) ?: ''),
            providerId: $orgId
        );

        return $entities;
    }
}