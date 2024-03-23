<?php

namespace App\Tests\TestGeneration\Group;

use App\Tests\TestGeneration\AssertStringOrder;
use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[TestDox('Group (App\Tests\TestGeneration\Group\GroupTest)')]
final class GroupTest extends PHPUnitTestCase
{
    use AssertStringOrder;
    use ScenarioFixture;

    #[Test]
    #[TestDox('When given $_dataName, then returns null')]
    #[DataProvider('nonRenderingScenarios')]
    public function testNonRenderingScenario(
        mixed $rawData,
    ): void {
        $subject = Group::from($rawData);

        $this->assertNull($subject);
    }

    public static function nonRenderingScenarios(): array
    {
        // All possible types in JSON, but not object
        // Any object without "cases"
        return [
            'an array' => [ [] ],
            'a bool' => [ true ],
            'a string' => [ 'some string' ],
            'an int' => [ 0 ],
            'a float' => [ 0.0 ],
            'null' => [ null ],
            'an empty object' => [ (object)[] ],
            'an object without "cases"' => [ (object)['some-property' => 'is not cases'] ],
        ];
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
            'When given an object with empty "cases" list, then renders an empty folding section'
                => [ 'empty-cases' ],
            // As we use InnerGroup to render the list, one test case is enough
            'When given an object with "cases" containing a testcase, then renders the cases list into folding section'
                => [ 'one-case-in-cases' ],

            'When given "cases" and "description", then renders multiline comment with description above cases list into folding section'
                => [ 'description' ],
        ];
    }

    private function subjectFor(string $scenario): ?Group
    {
        return Group::from($this->rawDataFor($scenario));
    }
}
