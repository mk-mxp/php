<?php

declare(strict_types=1);

namespace App\TrackData;

/**
 * Represents a list of 'cases'
 */
class InnerGroup
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

        $cases = [];
        foreach($rawData as $rawCase) {
            $case = TestCase::from($rawCase);
            if ($case === null)
                $case = Group::from($rawCase);
            if ($case === null)
                $case = Unknown::from($rawCase);
            $cases[] = $case;
        }

        return new static($cases);
    }

    public function renderPhpCode(): string
    {
        return \implode(self::LF, \array_map(
            fn ($case): string => $case->renderPhpCode(),
            $this->cases,
        ));
    }
}
