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
        return [
            'an array' => [ [] ],
            'a bool' => [ true ],
            'a string' => [ 'some string' ],
            'an int' => [ 0 ],
            'a float' => [ 0.0 ],
            'null' => [ null ],
        ];
    }

    private function subjectFor(string $scenario): ?Group
    {
        return Group::from($this->rawDataFor($scenario));
    }
}
