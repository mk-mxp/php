<?php

declare(strict_types=1);

namespace App\Tests;

use App\TomlParser;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class TomlParserTest extends TestCase
{
    #[TestDox('Golden test')]
    public function testGoldenTest(): void
    {
        $input = <<<'EOL'
        # This is an auto-generated file.
        #
        # Regenerating this file via `configlet sync` will:
        # - Recreate every `description` key/value pair
        # - Recreate every `reimplements` key/value pair, where they exist in problem-specifications
        # - Remove any `include = true` key/value pair (an omitted `include` key implies inclusion)
        # - Preserve any other key/value pair
        #
        # As user-added comments (using the # character) will be removed when this file
        # is regenerated, comments can be added via a `comment` key.

        [792a7082-feb7-48c7-b88b-bbfec160865e]
        description = "just some of the 'problem spec' things done"

        [2177e225-9ce7-40f6-b55d-fa420e62938e]
        description = "more - of what might ` be a \n problem"
        include = false
        reimplements = "792a7082-feb7-48c7-b88b-bbfec160865e"
        comment = "Skipped for whatever reason"

        EOL;
        $expected = [
            '792a7082-feb7-48c7-b88b-bbfec160865e' => [
                'description' => "just some of the 'problem spec' things done",
            ],
            '2177e225-9ce7-40f6-b55d-fa420e62938e' => [
                'comment' => 'Skipped for whatever reason',
                'description' => 'more - of what might ` be a \n problem',
                'include' => false,
                'reimplements' => '792a7082-feb7-48c7-b88b-bbfec160865e',
            ]
        ];

        $actual = TomlParser::parse($input);

        $this->assertEqualsCanonicalizing(array_keys($expected), array_keys($actual));
        $this->assertEqualsCanonicalizing(array_values($expected), array_values($actual));
    }

    #[TestDox('Unused features')]
    public function testUnusedFeatures(): void
    {
        $input = <<<'EOL'
        test = "root values"

        [table]
        integer = 123
        true-bool = true

        EOL;
        $expected = [
            'test' => "root values",
            'table' => [
                'integer' => 123,
                'true-bool' => true,
            ]
        ];

        $actual = TomlParser::parse($input);

        $this->assertEqualsCanonicalizing(array_keys($expected), array_keys($actual));
        $this->assertEqualsCanonicalizing(array_values($expected), array_values($actual));
    }
}
