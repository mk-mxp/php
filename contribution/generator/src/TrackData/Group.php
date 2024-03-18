<?php

declare(strict_types=1);

namespace App\TrackData;

/**
 * Represents a list of 'cases'
 */
class Group
{
    private function __construct(
        private ?array $data = null,
    ) {
    }

    public static function from(mixed $rawData): ?self
    {
        if (!\is_array($rawData))
            return null;

        return new static($rawData);
    }

    public function renderPhpCode(): string
    {
        return '';
    }
}
