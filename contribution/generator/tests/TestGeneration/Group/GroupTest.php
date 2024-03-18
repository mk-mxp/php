<?php

namespace App\Tests\TestGeneration\Group;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[TestDox('Group (App\Tests\TestGeneration\Group\GroupTest)')]
final class GroupTest extends PHPUnitTestCase
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
            'When given an object, then returns null'
                => [ 'object' ],
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
            'When given an empty list, then renders empty string'
                => [ 'empty-list' ],

            'When given one unknown item in list, then renders unknown item'
                => [ 'one-unknown-case' ],
            'When given many unknown items in list, then renders the items in order of input'
                => [ 'many-unknown-cases' ],

            'When given one test case in list, then renders the test case'
                => [ 'one-test-case' ],
        ];
    }

    private function subjectFor(string $scenario): ?Group
    {
        return Group::from($this->rawDataFor($scenario));
    }
}
