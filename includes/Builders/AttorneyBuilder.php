<?php

declare(strict_types=1);

namespace Ferraro\Schema\Builders;

use Ferraro\Schema\Contracts\BuilderInterface;
use Ferraro\Schema\Contracts\EntityInterface;
use Ferraro\Schema\Entities\ArticleEntity;
use Ferraro\Schema\Entities\ImageEntity;
use Ferraro\Schema\Entities\OrganizationEntity;
use Ferraro\Schema\Entities\PersonEntity;
use Ferraro\Schema\Entities\PostalAddressEntity;
use Ferraro\Schema\Entities\PracticeAreaEntity;
use Ferraro\Schema\Entities\ReviewEntity;
use Ferraro\Schema\Entities\VerdictEntity;
use Ferraro\Schema\Entities\WebPageEntity;
use Ferraro\Schema\Entities\OfferCatalogEntity;
use Ferraro\Schema\Graph\EntityRegistry;
use Ferraro\Schema\Parser\HtmlParser;
use Ferraro\Schema\Parser\RelationshipParser;

/**
 * Class AttorneyBuilder
 * * Orchestrates loading deep post mappings and relationships from 'team' Custom Post Types.
 */
final class AttorneyBuilder implements BuilderInterface
{
    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly HtmlParser $parser,
        private readonly RelationshipParser $relationParser
    ) {
    }

    public function supports(int $postId): bool
    {
        return get_post_type($postId) === 'team';
    }

    public function build(int $postId): array|EntityInterface|null
    {
        $post = get_post($postId);
        if (!$post) {
            return null;
        }

        $entities = [];

        // 1. Core ID Generation based on canonical Production URLs
        $canonicalUrl = rtrim(get_permalink($postId), '/');
        $personId = $canonicalUrl . '/#person';
        $webPageId = $canonicalUrl . '/#webpage';
        $imageId = $canonicalUrl . '/#image';
        
        $orgId = $this->registry->getOrganizationId();
        $addressId = $orgId . 'postaladdress';
        $offerCatalogId = $orgId . 'offer-catalog';

        // 2. WebPage Node
        $entities[] = new WebPageEntity(
            id: $webPageId,
            url: $canonicalUrl,
            name: trim(get_the_title($postId)) . ' | ' . get_bloginfo('name'),
            mainEntityId: $personId
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

        // 4. Resolve Relationship Arrays
        $reviews = $this->buildTestimonials($postId, $orgId);
        $reviewIds = array_map(fn($r) => $r->getId(), $reviews);
        
        $practiceAreas = $this->buildPracticeAreas($postId, $orgId);
        $serviceIds = array_map(fn($s) => $s->getId(), $practiceAreas);
        
        $relatedArticles = $this->buildRelatedArticles($postId);
        $pressReleases = $this->buildPressReleases($postId);
        $verdicts = $this->buildVerdicts($postId);

        // 5. Organization Node
        $entities[] = new OrganizationEntity(
            id: $orgId,
            name: 'Ferraro Law Firm',
            url: home_url('/'),
            addressId: $addressId,
            openingHours: null, // Omitted unless actual verified hours are known
            offerCatalogId: empty($serviceIds) ? null : $offerCatalogId,
            reviewIds: $reviewIds
        );

        // 6. Push relationship nodes to graph array
        foreach ($reviews as $review) $entities[] = $review;
        foreach ($relatedArticles as $article) $entities[] = $article;
        foreach ($pressReleases as $press) $entities[] = $press;
        foreach ($verdicts as $verdict) $entities[] = $verdict;

        // 7. Offer Catalog Node
        if (!empty($serviceIds)) {
            $entities[] = new OfferCatalogEntity(
                id: $offerCatalogId,
                name: 'Practice Areas',
                serviceIds: $serviceIds
            );
            foreach ($practiceAreas as $pa) $entities[] = $pa;
        }

        // 8. Image Node
        $imageEntity = $this->buildImageEntity($postId, $imageId);
        if ($imageEntity) {
            $entities[] = $imageEntity;
        }

        // 9. Process/Clean Text & Education
        $rawEducation = get_field('education', $postId);
        $alumniOfRaw = $this->parser->extractListItems($rawEducation);
        $alumniOfClean = [];
        
        foreach ($alumniOfRaw as $eduStr) {
            $school = $this->extractSchoolName($eduStr);
            if ($school) {
                $alumniOfClean[] = $school;
            }
        }

        // KnowsAbout mapping directly from Practice Area names
        $knowsAbout = array_map(fn($s) => $s->toArray()['name'], $practiceAreas);

        // 10. Core Person Node
        $entities[] = new PersonEntity(
            id: $personId,
            name: trim(get_the_title($postId)),
            jobTitle: trim(get_field('position', $postId) ?: ''),
            description: trim($this->parser->toPlainText(get_field('intro', $postId)) ?: ''),
            imageId: $imageEntity ? $imageId : null,
            url: $canonicalUrl,
            alumniOf: array_unique($alumniOfClean),
            awards: $this->parser->extractListItems(get_field('awards', $postId)),
            knowsAbout: $knowsAbout,
            knowsLanguage: $this->parser->extractListItems(get_field('foreign_language_content', $postId)),
            honorificSuffix: $this->parser->extractListItems(get_field('bar_admissions', $postId)),
            worksForId: $orgId
        );

        return $entities;
    }

    private function buildPracticeAreas(int $postId, string $orgId): array
    {
        $raw = get_field('practice_areas', $postId); 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            $id = rtrim($permalink, '/') . '/#service';

            $entities[] = new PracticeAreaEntity(
                id: $id,
                name: trim($post->post_title),
                url: $permalink,
                description: trim($post->post_excerpt ?: ''),
                providerId: $orgId
            );
        }

        return $entities;
    }

    private function buildRelatedArticles(int $postId): array
    {
        $raw = get_field('related_posts', $postId);
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            $id = rtrim($permalink, '/') . '/#article';
            $entities[] = new ArticleEntity(
                id: $id,
                type: 'BlogPosting',
                headline: trim($post->post_title),
                url: $permalink,
                datePublished: get_the_date('c', $post->ID) ?: null
            );
        }
        return $entities;
    }

    private function buildPressReleases(int $postId): array
    {
        $raw = get_field('related_press_media', $postId);
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];
        $archiveUrl = rtrim(home_url('/press-media/'), '/');

        foreach ($posts as $post) {
            $id = $archiveUrl . '/#' . $post->post_name;
            $entities[] = new ArticleEntity(
                id: $id,
                type: 'NewsArticle',
                headline: trim($post->post_title),
                url: $archiveUrl,
                datePublished: get_the_date('c', $post->ID) ?: null
            );
        }
        return $entities;
    }

    private function buildTestimonials(int $postId, string $orgId): array
    {
        $raw = get_field('testimonials', $postId); 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];
        $archiveUrl = rtrim(home_url('/testimonials/'), '/');

        foreach ($posts as $post) {
            $id = $archiveUrl . '/#' . $post->post_name;
            $body = get_field('testimonial', $post->ID) ?: $post->post_content;
            
            $entities[] = new ReviewEntity(
                id: $id,
                authorName: trim($post->post_title),
                reviewBody: trim($this->parser->toPlainText($body) ?: ''),
                ratingValue: 5.0,
                itemReviewedId: $orgId
            );
        }
        return $entities;
    }

    private function buildVerdicts(int $postId): array
    {
        $raw = get_field('related_verdicts', $postId);
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            $id = rtrim($permalink, '/') . '/#verdict';
            $amount = get_field('verdict_amount', $post->ID) ?: null;
            $description = get_field('verdict_description', $post->ID) ?: $post->post_excerpt;

            $entities[] = new VerdictEntity(
                id: $id,
                title: trim($post->post_title),
                description: trim($this->parser->toPlainText($description) ?: ''),
                amount: $amount
            );
        }
        return $entities;
    }

    private function buildImageEntity(int $postId, string $imageId): ?ImageEntity
    {
        $imageField = get_field('thumbnail_image', $postId); 
        if (empty($imageField)) return null;

        $imageUrl = '';
        $width = null;
        $height = null;
        $caption = null;

        if (is_array($imageField)) {
            $imageUrl = $imageField['url'] ?? '';
            $width = isset($imageField['width']) ? (int) $imageField['width'] : null;
            $height = isset($imageField['height']) ? (int) $imageField['height'] : null;
            $caption = $imageField['caption'] ?: ($imageField['alt'] ?: null);
        } elseif (is_numeric($imageField)) {
            $meta = wp_get_attachment_metadata((int) $imageField);
            $imageUrl = wp_get_attachment_url((int) $imageField) ?: '';
            $width = $meta['width'] ?? null;
            $height = $meta['height'] ?? null;
            $caption = wp_get_attachment_caption((int) $imageField) ?: null;
        } elseif (is_string($imageField)) {
            $imageUrl = $imageField;
        }

        if (empty($imageUrl) || str_contains(strtolower($imageUrl), 'bio-no-image')) {
            return null;
        }

        return new ImageEntity(
            id: $imageId,
            url: $imageUrl,
            width: $width,
            height: $height,
            caption: $caption
        );
    }

    /**
     * Parses complex education strings to locate just the primary school/university.
     */
    private function extractSchoolName(string $educationString): ?string
    {
        $string = trim($educationString);
        if (empty($string)) return null;
        
        // Exclude standalone strings that are purely concentrations 
        if (stripos($string, 'concentration') === 0) return null;
        
        $parts = explode(',', $string);
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/(University|College|School|Institute|Academy|Law Center)/i', $part)) {
                return $part;
            }
        }
        return $string; // Safe fallback
    }
}