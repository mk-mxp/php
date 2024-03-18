<?php

declare(strict_types=1);

namespace App\TrackData;

use App\TrackData\TestCase;
use App\TrackData\Unknown;

class CanonicalData
{
    /**
     * PHP_EOL is CRLF on Windows, we always want LF
     * @see https://www.php.net/manual/en/reserved.constants.php#constant.php-eol
     */
    private const LF = "\n";

    /**
     * @param TestCase[] $testCases
     * @param string[] $comments
     */
    public function __construct(
        public Group $cases,
        public array $comments = [],
        private ?object $unknown = null,
    ) {
    }

    public static function from(object $rawData): self
    {
        $comments = $rawData->comments ?? [];
        unset($rawData->comments);

        $cases = Group::from($rawData->cases ?? []);
        unset($rawData->cases);

        // Ignore "exercise" key (not required)
        unset($rawData->exercise);

        return new static(
            $cases,
            $comments,
            empty(\get_object_vars($rawData)) ? null : $rawData,
        );
    }

    public function renderPhpCode(
        string $testClassName,
        string $solutionFileName,
        string $solutionClassName,
    ): string
    {
        return \sprintf(
            $this->template(),
            $this->renderUnknownData(),
            $this->renderTests(),
            $this->renderComments(),
            $testClassName,
            $solutionFileName,
            $solutionClassName,
        );
    }

    /**
     * %1$s Unknow data
     * %2$s Pre-rendered list of tests
     * %3$s Comments for DocBlock
     * %4$s Test class name
     * %5$s Solution file name
     * %6$s Solution class name
     */
    private function template(): string
    {
        return \file_get_contents(__DIR__ . '/canonical-data.txt');
    }

    private function renderUnknownData(): string
    {
        if ($this->unknown === null)
            return '';
        return $this->asMultiLineComment([\json_encode($this->unknown)]);
    }

    private function renderTests(): string
    {
        $tests = $this->cases->renderPhpCode();

        return empty($tests) ? '' : $this->indent($tests) . self::LF;
    }

    private function renderComments(): string
    {
        return empty($this->comments) ? '' : $this->forBlockComment([...$this->comments, '', '']);
    }

    private function forBlockComment(array $lines): string
    {
        return \implode(self::LF . ' * ', $lines);
    }

    private function asMultiLineComment(array $lines): string
    {
        return self::LF
            . '/* Unknown data:' . self::LF
            . ' * ' . implode(self::LF . ' * ', $lines) . self::LF
            . ' */' . self::LF
            ;
    }

    private function indent(string $lines): string
    {
        $indent = '    ';
        return $indent . \implode(self::LF . $indent, \explode(self::LF, $lines));
    }
}
