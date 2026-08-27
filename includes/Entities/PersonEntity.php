<?php

declare(strict_types=1);

namespace Ferraro\Schema\Entities;

/**
 * Class PersonEntity
 * * Represents a Schema.org Person (specifically tailored for attorney custom post types).
 */
final class PersonEntity extends Entity
{
    public function __construct(
        string $id,
        private readonly string $name,
        private readonly ?string $jobTitle = null,
        private readonly ?string $description = null,
        private readonly ?string $imageId = null,
        private readonly ?string $url = null,
        private readonly array $alumniOf = [],
        private readonly array $awards = [],
        private readonly array $knowsAbout = [],
        private readonly array $knowsLanguage = [],
        private readonly array $honorificSuffix = [],
        private readonly ?string $worksForId = null
    ) {
        parent::__construct($id, 'Person'); 
    }

    public function toArray(): array
    {
        // Dynamically format clean school names as proper CollegeOrUniversity types
        $alumniOfFormatted = [];
        foreach ($this->alumniOf as $school) {
            $schoolName = trim($school);
            if ($schoolName !== '') {
                $alumniOfFormatted[] = [
                    '@type' => 'CollegeOrUniversity',
                    'name' => $schoolName
                ];
            }
        }

        return [
            '@type' => $this->getType(),
            '@id' => $this->getId(),
            'name' => $this->name,
            'jobTitle' => $this->jobTitle,
            'description' => $this->description,
            'image' => $this->imageId ? ['@id' => $this->imageId] : null,
            'url' => $this->url,
            'alumniOf' => $alumniOfFormatted,
            'award' => $this->awards,
            'knowsAbout' => array_unique($this->knowsAbout),
            'knowsLanguage' => $this->knowsLanguage,
            'honorificSuffix' => $this->honorificSuffix,
            'worksFor' => $this->worksForId ? ['@id' => $this->worksForId] : null,
        ];
    }
}