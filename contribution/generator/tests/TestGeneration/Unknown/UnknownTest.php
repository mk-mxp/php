<?php

namespace App\Tests\TestGeneration\TestCase;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\CanonicalData\Unknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[TestDox('Unknown (App\Tests\TestGeneration\TestCase\UnknownTest)')]
final class UnknownTest extends PHPUnitTestCase
{
    use ScenarioFixture;

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('renderingScenarios')]
    public function testRenderingScenario(
        string $scenario,
    ): void {
        $expected =  $this->expectedFor($scenario);
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode('fallback_method_name');

        $this->assertStringContainsString($expected, $actual);
    }

    public static function renderingScenarios(): array
    {
        return [
            'When given an empty object, then renders multiline comment with JSON'
                => [ 'empty-object' ],
            'When given any object, then renders multiline comment with JSON'
                => [ 'any-object' ],
        ];
    }

    private function subjectFor(string $scenario): Unknown
    {
        return Unknown::from($this->rawDataFor($scenario));
    }
}
