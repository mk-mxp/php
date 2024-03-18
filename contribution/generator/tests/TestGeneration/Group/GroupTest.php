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
            'When given many unknown items in list, then renders all items'
                => [ 'many-unknown-cases' ],

            'When given one test case in list, then renders the test case'
                => [ 'one-test-case' ],
            'When given many test cases in list, then renders all test cases'
                => [ 'many-test-cases' ],
        ];
    }

    #[Test]
    #[TestDox('When given many unknown items in list, then renders the unknown items in order of input')]
    public function testRenderingUnknownOrder(): void
    {
        $subject = $this->subjectFor('many-unknown-cases');

        $actual = $subject->renderPhpCode();

        $this->assertStringContainsStringBeforeString(
            '"an-unknown-item"',
            '"another-unknown-item"',
            $actual,
        );
        $this->assertStringContainsStringBeforeString(
            '"another-unknown-item"',
            '"a-last-unknown-item"',
            $actual,
        );
    }

    #[Test]
    #[TestDox('When given many test cases in list, then renders the test cases in order of input')]
    public function testRenderingTestCaseOrder(): void
    {
        $subject = $this->subjectFor('many-test-cases');

        $actual = $subject->renderPhpCode();

        $this->assertStringContainsStringBeforeString(
            'uuid: 31a673f2-5e54-49fe-bd79-1c1dae476c9c',
            'uuid: 4f99b933-367b-404b-8c6d-36d5923ee476',
            $actual,
        );
        $this->assertStringContainsStringBeforeString(
            'uuid: 4f99b933-367b-404b-8c6d-36d5923ee476',
            'uuid: 91122d10-5ec7-47cb-b759-033756375869',
            $actual,
        );
    }

    private function subjectFor(string $scenario): ?Group
    {
        return Group::from($this->rawDataFor($scenario));
    }
}
