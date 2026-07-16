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

    /**
     * @inheritDoc
     */
    public function supports(int $postId): bool
    {
        return get_post_type($postId) === 'team';
    }

    /**
     * @inheritDoc
     */
    public function build(int $postId): ?EntityInterface
    {
        $post = get_post($postId);
        if (!$post) {
            return null;
        }

        // 1. Core Meta Mapping
        $canonicalUrl = get_permalink($postId);
        $entityId = $this->registry->getEntityId('team', $post->post_name, 'person');
        $name = get_the_title($postId);
        $jobTitle = get_field('position', $postId) ?: null; // ACF Field "position" 
        $introText = get_field('intro', $postId); // ACF Field "intro" 
        $description = $this->parser->toPlainText($introText);

        // 2. Base Assets
        $imageEntity = $this->buildImageEntity($postId, $post->post_name);
        $organization = $this->buildOrganizationEntity();

        // 3. Simple WYSIWYG List Parsers
        $educationHtml = get_field('education', $postId); // ACF Tab "Education"
        $alumniOf = $this->parser->extractListItems($educationHtml);

        $awardsHtml = get_field('awards', $postId); // ACF Tab "Awards"
        $awards = $this->parser->extractListItems($awardsHtml);

        $languagesHtml = get_field('foreign_language_content', $postId); // ACF Tab "Foreign Languages"
        $languages = $this->parser->extractListItems($languagesHtml);

        $barAdmissionsHtml = get_field('bar_admissions', $postId) ?: get_field('bar admissions', $postId); // ACF Tab "Bar Admissions"
        $barAdmissions = $this->parser->extractListItems($barAdmissionsHtml);

        // 4. Resolve Relationship Connections (Sprint 5) 
        $practiceAreas = $this->buildPracticeAreas($postId);
        $relatedArticles = $this->buildRelatedArticles($postId);
        $pressReleases = $this->buildPressReleases($postId);
        $testimonials = $this->buildTestimonials($postId);
        $verdicts = $this->buildVerdicts($postId);

        // 5. Build Entity Graph Object
        return new PersonEntity(
            id: $entityId,
            name: $name,
            jobTitle: $jobTitle,
            description: $description,
            image: $imageEntity,
            url: $canonicalUrl,
            alumniOf: $alumniOf,
            awards: $awards,
            knowsAbout: [],
            knowsLanguage: $languages,
            honorificSuffix: $barAdmissions,
            worksFor: $organization,
            practiceAreas: $practiceAreas,
            relatedArticles: $relatedArticles,
            pressReleases: $pressReleases,
            testimonials: $testimonials,
            verdicts: $verdicts
        );
    }

    /**
     * Resolves the "practice_areas" Relationship field into PracticeArea Entities. 
     *
     * @param int $postId
     * @return array<PracticeAreaEntity>
     */
    private function buildPracticeAreas(int $postId): array
    {
        $raw = get_field('practice_areas', $postId); // ACF field "practice_areas" 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $id = $this->registry->getEntityId('practice-areas', $post->post_name, 'service');
            $entities[] = new PracticeAreaEntity(
                id: $id,
                name: $post->post_title,
                url: get_permalink($post->ID),
                description: $post->post_excerpt ?: null,
                provider: $this->buildOrganizationEntity()
            );
        }

        return $entities;
    }

    /**
     * Resolves the "related_posts" Relationship field into Article Entities. 
     *
     * @param int $postId
     * @return array<ArticleEntity>
     */
    private function buildRelatedArticles(int $postId): array
    {
        $raw = get_field('related_posts', $postId); // ACF field "related_posts" 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $id = $this->registry->getEntityId('post', $post->post_name, 'article');
            $entities[] = new ArticleEntity(
                id: $id,
                type: 'BlogPosting',
                headline: $post->post_title,
                url: get_permalink($post->ID),
                datePublished: get_the_date('c', $post->ID) ?: null
            );
        }

        return $entities;
    }

    /**
     * Resolves the "related_press_media" Relationship field into Article Entities. 
     *
     * @param int $postId
     * @return array<ArticleEntity>
     */
    private function buildPressReleases(int $postId): array
    {
        $raw = get_field('related_press_media', $postId); // ACF field "related_press_media" 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $id = $this->registry->getEntityId('press-and-media', $post->post_name, 'newsarticle');
            $entities[] = new ArticleEntity(
                id: $id,
                type: 'NewsArticle',
                headline: $post->post_title,
                url: get_permalink($post->ID),
                datePublished: get_the_date('c', $post->ID) ?: null
            );
        }

        return $entities;
    }

    /**
     * Resolves the "testimonials" Relationship field into Review Entities. 
     *
     * @param int $postId
     * @return array<ReviewEntity>
     */
    private function buildTestimonials(int $postId): array
    {
        $raw = get_field('testimonials', $postId); // ACF field "testimonials" 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $id = $this->registry->getEntityId('testimonials', $post->post_name, 'review');
            $body = get_field('testimonial', $post->ID) ?: $post->post_content;
            
            $entities[] = new ReviewEntity(
                id: $id,
                authorName: $post->post_title,
                reviewBody: $this->parser->toPlainText($body) ?: '',
                itemReviewed: $this->buildOrganizationEntity()
            );
        }

        return $entities;
    }

    /**
     * Resolves the "related_verdicts" Relationship field into Verdict Entities. 
     *
     * @param int $postId
     * @return array<VerdictEntity>
     */
    private function buildVerdicts(int $postId): array
    {
        $raw = get_field('related_verdicts', $postId); // ACF field "related_verdicts" 
        $posts = $this->relationParser->parseRelationships($raw);
        $entities = [];

        foreach ($posts as $post) {
            $id = $this->registry->getEntityId('verdicts', $post->post_name, 'verdict');
            $amount = get_field('verdict_amount', $post->ID) ?: null;
            $description = get_field('verdict_description', $post->ID) ?: $post->post_excerpt;

            $entities[] = new VerdictEntity(
                id: $id,
                title: $post->post_title,
                description: $this->parser->toPlainText($description),
                amount: $amount
            );
        }

        return $entities;
    }

    /**
     * Builds standard ImageEntity from raw/array values of ACF thumbnail.
     *
     * @param int $postId
     * @param string $slug
     * @return ImageEntity|null
     */
    private function buildImageEntity(int $postId, string $slug): ?ImageEntity
    {
        $imageField = get_field('thumbnail_image', $postId); // ACF field "thumbnail_image" 
        if (empty($imageField)) {
            return null;
        }

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

        if (empty($imageUrl)) {
            return null;
        }

        $imageId = $this->registry->getEntityId('team', $slug, 'image');

        return new ImageEntity(
            id: $imageId,
            url: $imageUrl,
            width: $width,
            height: $height,
            caption: $caption
        );
    }

    /**
     * Helper to return consistent parent Firm Organization / Local Business.
     *
     * @return OrganizationEntity
     */
    private function buildOrganizationEntity(): OrganizationEntity
    {
        // Define the structured firm physical address
        $address = new PostalAddressEntity(
            id: 'https://stg-ferraronewsite-stage.kinsta.cloud/#postaladdress',
            streetAddress: '600 Brickell Ave Unit 3800',
            postalCode: '33131',
            addressLocality: 'Miami',
            addressRegion: 'Florida',
            addressCountry: 'US'
        );

        return new OrganizationEntity(
            id: $this->registry->getOrganizationId(),
            name: get_bloginfo('name'),
            url: home_url('/'),
            logo: null,
            sameAs: [],
            address: $address,
            openingHours: 'Mo-Su 00:00-24:00' // SET TO ALWAYS OPEN (24/7)
        );
    }
}