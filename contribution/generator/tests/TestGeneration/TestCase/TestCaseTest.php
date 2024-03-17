<?php

namespace App\Tests\TestGeneration\TestCase;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\CanonicalData\TestCase;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[TestDox('Test Case (App\Tests\TestGeneration\TestCase\TestCaseTest)')]
final class TestCaseTest extends PHPUnitTestCase
{
    use ScenarioFixture;

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('nonRenderingScenarios')]
    public function testNonRenderingScenario(
        string $scenario,
    ): void {
        $subject = $this->subjectFor($scenario);

        $this->assertNull($subject);
    }

    public static function nonRenderingScenarios(): array
    {
        return [
            'When given an empty object, then returns null'
                => [ 'empty-object' ],
            'When given object without "uuid", then returns null'
                => [ 'no-uuid' ],
            'When given object without "description", then returns null'
                => [ 'no-description' ],
            'When given object without "property", then returns null'
                => [ 'no-property' ],
            'When given object without "input", then returns null'
                => [ 'no-input' ],
            'When given object without "expected", then returns null'
                => [ 'no-expected' ],
        ];
    }

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('renderingScenarios')]
    public function testRenderingScenario(
        string $scenario,
    ): void {
        $expected =  $this->expectedFor($scenario);
        $subject = $this->subjectFor($scenario);

        $actual = $subject->asClassMethods();

        $this->assertStringContainsString($expected, $this->toPhpCode($actual));
    }

    public static function renderingScenarios(): array
    {
        return [
            'When given an object with all required properties, then renders method'
                => [ 'all-required' ],
            'When given an object with all required properties and an unknown one, then renders method with JSON in DocBlock'
                => [ 'all-required-with-unknown' ],
            'When given a valid object with problematic chars in description, then renders method name without those'
                => [ 'all-required-with-problematic-description' ],
        ];
    }

    private function subjectFor(string $scenario): ?TestCase
    {
        return TestCase::from($this->rawDataFor($scenario));
    }

    private function toPhpCode(array $statements): string
    {
        return (new Standard())->prettyPrint($statements);
    }
}
