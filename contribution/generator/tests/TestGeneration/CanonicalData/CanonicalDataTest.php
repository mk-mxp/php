<?php

namespace App\Tests\TestGeneration\CanonicalData;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\CanonicalData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Canonical Data (App\Tests\TestGeneration\CanonicalData\CanonicalDataTest)')]
final class CanonicalDataTest extends TestCase
{
    use ScenarioFixture;

    #[Test]
    #[TestDox('When given a different test class name, then renders that test class name into stub')]
    public function rendersTestClassName(): void {
        $scenario = 'different-test-class-name';
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'DifferentTestClassName',
            'SomeSolutionFile.ext',
            'SomeSolutionClass',
        );

        $this->assertStringContainsAllOfScenario($scenario, $actual);
    }

    #[Test]
    #[TestDox('When given a different solution file name, then renders that file name into stub')]
    public function rendersSolutionFileName(): void {
        $scenario = 'different-solution-file-name';
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'SomeTestClass',
            'DifferentSolutionFile.php',
            'SomeSolutionClass',
        );

        $this->assertStringContainsAllOfScenario($scenario, $actual);
    }

    #[Test]
    #[TestDox('When given a different solution class name, then renders that class name into stub')]
    public function rendersSolutionClassName(): void {
        $scenario = 'different-solution-class-name';
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'SomeTestClass',
            'SomeSolutionFile.ext',
            'DifferentSolutionClassName',
        );

        $this->assertStringContainsAllOfScenario($scenario, $actual);
    }

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('renderingScenarios')]
    public function testRenderingScenario(
        string $scenario,
    ): void {
        $subject = $this->subjectFor($scenario);

        $actual = $subject->renderPhpCode(
            'SomeTestClass',
            'SomeSolutionFile.ext',
            'SomeSolutionClass',
        );

        $this->assertStringContainsAllOfScenario($scenario, $actual);
    }

    public static function renderingScenarios(): array
    {
        return [
            // This scenario asserts on the constant parts and their position in relation to the varying part(s)
            'When given a valid object with all keys, then renders all non-varying parts where they belong'
                => [ 'non-varying-parts' ],

            // These scenarios assert on the varying part(s)

            // "exercise" is tricky to test for. It never changes anything.
            // "exercise" and "no-exercise" is therefore covered by both
            // equalling the complete "empty-object" rendering, as this is the
            // smallest possible literal to compare to.
            'When given an empty object, then renders only test class stub'
                => [ 'empty-object' ], // includes "no-exercise"
            'When given object with only "exercise", then renders like empty object'
                => [ 'exercise' ],

            'When given object with no unknown key, then renders no multi-line comment'
                => [ 'no-unknown-key' ],
            'When given object with an unknown key, then renders the key as JSON in multi-line comment'
                => [ 'one-unknown-key' ],
            'When given object with many unknown keys, then renders all keys as JSON in multi-line comment'
                => [ 'many-unknown-keys' ],

            'When given a valid object with no "comments", then renders no comments part'
                => [ 'no-comments' ],
            'When given object with singleline "comments", then renders comment in class DocBlock'
                => [ 'one-line-comments' ],
            'When given object with multiline "comments", then renders test class with comments in class DocBlock'
                => [ 'many-line-comments' ],

            // Here we need to check for rendering / not rendering only
            'When given a valid object with no "cases", then renders no cases'
                => [ 'no-cases' ],
            'When given a valid object with "cases", then renders cases'
                => [ 'cases' ],
        ];
    }

    private function subjectFor(string $scenario): CanonicalData
    {
        return CanonicalData::from($this->rawDataFor($scenario));
    }
}
