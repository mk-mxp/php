<?php

declare(strict_types=1);

namespace App\TrackData\CanonicalData;

/**
 * Represents a 'cases' entry, that is not one of the known types
 */
class Unknown
{
    private function __construct(
        private ?object $data = null,
    ) {
    }

    public static function from(object $rawData): self
    {
        return new static($rawData);
    }

    public function renderPhpCode(): string
    {
        return \sprintf(
            $this->template(),
            \json_encode($this->data),
        );
    }

    private function template(): string
    {
        return \file_get_contents(__DIR__ . '/unknown.txt');
    }
}
