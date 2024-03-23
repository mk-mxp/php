<?php

declare(strict_types=1);

namespace App\TrackData;

use App\TrackData\Item;

/**
 * Represents a 'cases' entry, that is not one of the known types
 */
class Unknown implements Item
{
    private function __construct(
        private ?object $data = null,
    ) {
    }

    public static function from(mixed $rawData): ?static
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
