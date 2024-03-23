<?php

declare(strict_types=1);

namespace App\TrackData;

/**
 * Represents a folding section of thematically connected 'cases'
 */
class Group
{
    /**
     * PHP_EOL is CRLF on Windows, we always want LF
     * @see https://www.php.net/manual/en/reserved.constants.php#constant.php-eol
     */
    private const LF = "\n";

    /**
     * @param string[] $comments
     */
    private function __construct(
        private InnerGroup $cases,
        private string $description = '',
        private array $comments = [],
    ) {
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

        return new static(
            InnerGroup::from($rawData->cases),
            $rawData->description ?? '',
            $rawData->comments ?? [],
        );
    }

    public function renderPhpCode(): string
    {
        return \sprintf(
            $this->template(),
            $this->renderTests(),
            $this->renderComments(),
        );
    }

    /**
     * %1$s Pre-rendered list of tests
     * %2$s Multiline comment
     */
    private function template(): string
    {
        return \file_get_contents(__DIR__ . '/group.txt');
    }

    private function renderTests(): string
    {
        $tests = $this->cases->renderPhpCode();

        return empty($tests) ? '' : $tests . self::LF . self::LF;
    }

    private function renderComments(): string
    {
        $lines = [];
        if (!empty($this->description)) {
            $lines[] = $this->description;
        }
        $lines = [...$lines, ...$this->comments];

        return empty($lines) ? '' : $this->asMultiLineComment($lines);
    }

    private function asMultiLineComment(array $lines): string
    {
        return self::LF
            . '/*' . self::LF
            . ' * ' . implode(self::LF . ' * ', $lines) . self::LF
            . ' */' . self::LF
            ;
    }
}
