<?php

declare(strict_types=1);

namespace Ferraro\Schema\Builders;

use Ferraro\Schema\Contracts\BuilderInterface;
use Ferraro\Schema\Contracts\EntityInterface;
use Ferraro\Schema\Entities\ArticleEntity; // Assuming this was created based on AttorneyBuilder reference
use Ferraro\Schema\Entities\OrganizationEntity;
use Ferraro\Schema\Entities\PersonEntity;
use Ferraro\Schema\Entities\WebPageEntity;
use Ferraro\Schema\Graph\EntityRegistry;

/**
 * Class PostBuilder
 * * Builds the flat @graph schema for standard blog posts.
 */
final class PostBuilder implements BuilderInterface
{
    public function __construct(
        private readonly EntityRegistry $registry
    ) {
    }

    public function supports(int $postId): bool
    {
        return get_post_type($postId) === 'post';
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
        $articleId = $canonicalUrl . '/#article';
        $webPageId = $canonicalUrl . '/#webpage';
        $orgId = $this->registry->getOrganizationId();
        
        // Author details
        $authorId = $post->post_author;
        $authorCanonicalId = home_url('/author/' . get_the_author_meta('user_nicename', $authorId)) . '/#person';

        // 2. WebPage Node
        $entities[] = new WebPageEntity(
            id: $webPageId,
            url: $canonicalUrl,
            name: trim(get_the_title($postId)) . ' | ' . get_bloginfo('name'),
            mainEntityId: $articleId
        );

        // 3. Organization (Publisher) Node
        $entities[] = new OrganizationEntity(
            id: $orgId,
            name: 'Ferraro Law Firm',
            url: home_url('/')
        );

        // 4. Author Node (Person)
        $entities[] = new PersonEntity(
            id: $authorCanonicalId,
            name: get_the_author_meta('display_name', $authorId),
            url: home_url('/author/' . get_the_author_meta('user_nicename', $authorId))
        );

        // 5. Core Article Node
        // Note: Assuming your ArticleEntity accepts these parameters based on standard Schema.org specs
        $entities[] = new ArticleEntity(
            id: $articleId,
            type: 'BlogPosting',
            headline: trim(get_the_title($postId)),
            url: $canonicalUrl,
            datePublished: get_the_date('c', $postId) ?: null,
            dateModified: get_the_modified_date('c', $postId) ?: null,
            authorId: $authorCanonicalId,
            publisherId: $orgId
        );

        return $entities;
    }
}