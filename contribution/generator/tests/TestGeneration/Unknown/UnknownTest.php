<?php

namespace App\Tests\TestGeneration\Unknown;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\Item;
use App\TrackData\Unknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[TestDox('Unknown (App\Tests\TestGeneration\Unknown\UnknownTest)')]
final class UnknownTest extends PHPUnitTestCase
{
    use ScenarioFixture;

    #[Test]
    public function implementsItemInterface(): void
    {
        $this->assertInstanceOf(Item::class, Unknown::from((object)[]));
    }

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('renderingScenarios')]
    public function testRenderingScenario(
        string $scenario,
    ): void {
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode();

        $this->assertStringContainsAllOfScenario($scenario, $actual);
    }

    public static function renderingScenarios(): array
    {
        return [
            // This scenario asserts on the constant parts and their position in relation to the varying part(s)
            'When given an empty object, then renders multiline comment with JSON'
                => [ 'multiline-comment-with-json' ],
            // These scenarios assert on the varying part(s)
            'When given an empty object, then renders it as JSON for a multiline comment'
                => [ 'empty-object' ],
            'When given any object, then renders it as JSON for a multiline comment'
                => [ 'any-object' ],
        ];
    }

    private function subjectFor(string $scenario): Unknown
    {
        return Unknown::from($this->rawDataFor($scenario));
    }
}
