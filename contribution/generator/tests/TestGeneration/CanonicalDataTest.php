<?php

namespace App\Tests\TestGeneration;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\CanonicalData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Canonical Data (App\Tests\TestGeneration\CanonicalDataTest)')]
final class CanonicalDataTest extends TestCase
{
    use ScenarioFixture;

    #[Test]
    #[TestDox('When given a different test class name, then renders that test class name into stub')]
    public function rendersTestClassName(): void {
        $scenario = 'different-test-class-name';
        $expected =  $this->expectedFor($scenario);
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'DifferentTestClassName',
            'SomeSolutionFile.ext',
            'SomeSolutionClass',
        );

        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    #[TestDox('When given a different solution file name, then renders that file name into stub')]
    public function rendersSolutionFileName(): void {
        $scenario = 'different-solution-file-name';
        $expected =  $this->expectedFor($scenario);
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'SomeTestClass',
            'DifferentSolutionFile.php',
            'SomeSolutionClass',
        );

        $this->assertStringContainsString($expected, $actual);
    }

    // TODO: Add test for varying solution class name

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('renderingScenarios')]
    public function testRenderingScenario(
        string $scenario,
    ): void {
        $expected =  $this->expectedFor($scenario);
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'SomeTestClass',
            'SomeSolutionFile.ext',
            'SomeSolutionClass',
        );

        $this->assertStringContainsString($expected, $actual);
    }

    public static function renderingScenarios(): array
    {
        return [
            'When given an empty object, then renders only test class stub'
                => [ 'empty-object' ],

            'When given object with "exercise", then ignores it'
                => [ 'ignore-exercise' ],
            'When given object with only unknown keys, then renders JSON in multi-line comment'
                => [ 'only-unknown-keys' ],
            'When given object with singleline "comments", then renders test class with comment in class DocBlock'
                => [ 'only-singleline-comment' ],
            'When given object with multiline "comments", then renders test class with comments in class DocBlock'
                => [ 'only-multiline-comments' ],
            'When given object with one unknown item in "cases", then renders the item into the test class stub'
                => [ 'one-unknown-case' ],
            'When given object with many unknown items in "cases", then renders the items into the test class stub'
                => [ 'many-unknown-cases' ],
            'When given object with one test case in "cases", then renders the test case into the test class stub'
                => [ 'one-test-case' ],
            'When given object with many test cases in "cases", then renders the test cases into the test class stub'
                => [ 'many-test-cases' ],
            'When given object with mixed test case and unknown in "cases", then renders everything in order of input into the test class stub'
                => [ 'mixed-up-cases' ],
        ];
    }

    private function subjectFor(string $scenario): CanonicalData
    {
        return CanonicalData::from($this->rawDataFor($scenario));
    }
}
