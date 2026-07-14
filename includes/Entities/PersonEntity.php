<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class PersonEntity
 * * Represents a Schema.org Person (specifically tailored for attorney custom post types).
 */
final class PersonEntity extends Entity
{
    /**
     * @param string $id
     * @param string $name
     * @param string|null $jobTitle
     * @param string|null $description
     * @param ImageEntity|null $image
     * @param string|null $url
     * @param array<string> $alumniOf
     * @param array<string> $awards
     * @param array<string> $knowsAbout
     * @param array<string> $knowsLanguage
     * @param array<string> $honorificSuffix
     * @param OrganizationEntity|null $worksFor
     * @param array<PracticeAreaEntity> $practiceAreas Linked Practice Area Entities
     * @param array<ArticleEntity> $relatedArticles Linked Blog Post Entities
     * @param array<ArticleEntity> $pressReleases Linked Press Release/Media Entities
     * @param array<ReviewEntity> $testimonials Linked Client Testimonial Entities
     * @param array<VerdictEntity> $verdicts Linked Case Result Entities
     */
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly ?string $jobTitle = null,
        private readonly ?string $description = null,
        private readonly ?ImageEntity $image = null,
        private readonly ?string $url = null,
        private readonly array $alumniOf = [],
        private readonly array $awards = [],
        private readonly array $knowsAbout = [],
        private readonly array $knowsLanguage = [],
        private readonly array $honorificSuffix = [],
        private readonly ?OrganizationEntity $worksFor = null,
        private readonly array $practiceAreas = [],
        private readonly array $relatedArticles = [],
        private readonly array $pressReleases = [],
        private readonly array $testimonials = [],
        private readonly array $verdicts = []
    ) {
        parent::__construct($id, 'Attorney');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->name,
            'jobTitle' => $this->jobTitle,
            'description' => $this->description,
            'image' => $this->serializeValue($this->image),
            'url' => $this->url,
            'alumniOf' => $this->alumniOf,
            'award' => $this->awards,
            'knowsAbout' => array_unique(array_merge(
                $this->knowsAbout,
                array_map(fn(PracticeAreaEntity $area) => $area->toArray()['name'], $this->practiceAreas)
            )),
            'knowsLanguage' => $this->knowsLanguage,
            'honorificSuffix' => $this->honorificSuffix,
            'worksFor' => $this->serializeValue($this->worksFor),
            'hasOfferCatalog' => !empty($this->practiceAreas) ? [
                '@type' => 'OfferCatalog',
                'name' => 'Practice Areas',
                'itemListElement' => $this->serializeValue($this->practiceAreas),
            ] : null,
            'subjectOf' => array_merge(
                $this->serializeValue($this->relatedArticles),
                $this->serializeValue($this->pressReleases)
            ),
            'review' => $this->serializeValue($this->testimonials),
            'publishingPrinciples' => $this->serializeValue($this->verdicts), // Mapping Case Verdicts as structured principles
        ];
    }
}