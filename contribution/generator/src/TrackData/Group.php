<?php

declare(strict_types=1);

namespace App\TrackData;

/**
 * Represents a list of 'cases'
 */
class Group
{
    /**
     * PHP_EOL is CRLF on Windows, we always want LF
     * @see https://www.php.net/manual/en/reserved.constants.php#constant.php-eol
     */
    private const LF = "\n";

    private function __construct(
        private array $cases,
    ) {
    }

    public static function from(mixed $rawData): ?self
    {
        if (!\is_array($rawData))
            return null;

        $testCases = [];
        foreach($rawData as $rawCase) {
            $thisCase = TestCase::from($rawCase);
            if ($thisCase === null)
                $thisCase = Unknown::from($rawCase);
            $testCases[] = $thisCase;
        }

        return new static($testCases);
    }

    public function renderPhpCode(): string
    {
        return \implode(self::LF, \array_map(
            fn ($case): string => $case->renderPhpCode(),
            $this->cases,
        ));
    }
}
