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
            'When given an empty list, then renders nothing'
                => [ 'empty-list' ],
        ];
    }

    private function subjectFor(string $scenario): Group
    {
        return Group::from($this->rawDataFor($scenario));
    }
}
