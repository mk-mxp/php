<?php

declare(strict_types=1);

namespace App\TrackData;

use App\TrackData\Item;

class TestCase implements Item
{
    /**
     * PHP_EOL is CRLF on Windows, we always want LF
     * @see https://www.php.net/manual/en/reserved.constants.php#constant.php-eol
     */
    private const LF = "\n";

    public function __construct(
        private string $uuid,
        private string $description,
        private string $property,
        private object $input,
        private mixed $expected,
        private array $comments = [],
        private ?object $unknown = null,
    ) {
    }

    public static function from(mixed $rawData): ?Item
    {
        $requiredProperties = [
            'uuid',
            'description',
            'property',
            'input',
            'expected',
        ];
        $actualProperties = \array_keys(\get_object_vars($rawData));
        $data = [];
        foreach ($requiredProperties as $requiredProperty) {
            if (!\in_array($requiredProperty, $actualProperties)) {
                return null;
            }
            $data[$requiredProperty] = $rawData->{$requiredProperty};
            unset($rawData->{$requiredProperty});
        }

        return new static(
            ...$data,
            unknown: empty(\get_object_vars($rawData)) ? null : $rawData,
        );
    }

    public function renderPhpCode(): string
    {
        return \sprintf(
            $this->template(),
            $this->testMethodName(),
            $this->renderUnknownData(),
            $this->indentTrailingLines(\var_export((array)$this->input, true)),
            $this->indentTrailingLines(\var_export($this->expected, true)),
            $this->uuid,
            \ucfirst($this->description),
            $this->property,
        );
    }

    /**
     * %1$s Method name
     * %2$s Unknow data
     * %3$s Input data
     * %4$s Expected data
     * %5$s UUID
     * %6$s testdox
     * %7$s property (method to call)
     */
    private function template(): string
    {
        return \file_get_contents(__DIR__ . '/test-case.txt');
    }

    private function testMethodName(): string
    {
        $sanitizedDescription = \preg_replace('/\W+/', ' ', $this->description);

        $methodNameParts = \explode(' ', $sanitizedDescription);
        $upperCasedParts = \array_map(
            fn ($part) => \ucfirst($part),
            $methodNameParts
        );

        return \lcfirst(\implode('', $upperCasedParts));
    }

    private function renderUnknownData(): string
    {
        if ($this->unknown === null)
            return '';
        return 'Unknown data:' . self::LF
            . ' * ' . \json_encode($this->unknown) . self::LF
            . ' * ' . self::LF
            . ' * '
            ;
    }

    private function indentTrailingLines(string $lines): string
    {
        $indent = '    ';
        return \implode(self::LF . $indent, \explode(self::LF, $lines));
    }
}
