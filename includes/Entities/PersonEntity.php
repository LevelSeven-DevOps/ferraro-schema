<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class PersonEntity
 * * Represents a Schema.org Person (specifically tailored for attorney custom post types).
 * Includes fields that match the custom fields parsed from ACF.
 */
final class PersonEntity extends Entity
{
    /**
     * @param string $id
     * @param string $name
     * @param string|null $jobTitle
     * @param string|null $description Short bio / introduction.
     * @param ImageEntity|null $image
     * @param string|null $url Professional Profile URL.
     * @param array<string> $alumniOf Schools attended.
     * @param array<string> $awards Recognitions and accolades.
     * @param array<string> $knowsAbout Expertise and practice areas.
     * @param array<string> $knowsLanguage Languages spoken.
     * @param array<string> $honorificSuffix (e.g., 'Esq.')
     * @param OrganizationEntity|null $worksFor The employing law firm.
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
        private readonly ?OrganizationEntity $worksFor = null
    ) {
        parent::__construct($id, 'Attorney'); // Specifically utilizes Attorney schema subtype of Person.
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
            'knowsAbout' => $this->knowsAbout,
            'knowsLanguage' => $this->knowsLanguage,
            'honorificSuffix' => $this->honorificSuffix,
            'worksFor' => $this->serializeValue($this->worksFor),
        ];
    }
}