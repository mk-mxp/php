<?php

declare(strict_types=1);

namespace App\TrackData;

/**
 * Represents a section of thematically connected 'cases'
 */
class Group
{
    /**
     * PHP_EOL is CRLF on Windows, we always want LF
     * @see https://www.php.net/manual/en/reserved.constants.php#constant.php-eol
     */
    private const LF = "\n";

    private function __construct() {
    }

    public static function from(mixed $rawData): ?self
    {
        if (
            ! (
                \is_object($rawData)
                && isset($rawData->cases)
            )
        ) {
            return null;
        }

        return new static();
    }

    public function renderPhpCode(): string
    {
        return $this->template();
    }

    private function template(): string
    {
        return \file_get_contents(__DIR__ . '/group.txt');
    }
}
