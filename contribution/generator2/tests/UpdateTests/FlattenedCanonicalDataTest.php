<?php

namespace App\Tests\UpdateTests;

use App\Tests\UpdateTests\ScenarioFixture;
use App\TrackData\FlattenedCanonicalData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * The problem specification has no `testClassName`, `solutionFileName`,
 * `solutionClassName` fields, these are added by the exercise.
 */
#[TestDox('Flattened Canonical Data (App\Tests\UpdateTests)')]
final class FlattenedCanonicalDataTest extends TestCase
{
    use ScenarioFixture;

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('happyScenarios')]
    public function testHappyScenario(
        string $scenario,
    ): void {
        $subject = $this->subjectFor($scenario);

        $this->assertJsonStringEqualsOutputFixture(
            $scenario,
            \json_encode($subject, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR),
            $scenario,
        );
    }

    /** @return array<string, string[]> */
    public static function happyScenarios(): array
    {
        return [
            'When given a valid object with all keys, then forwards empty cases array'
                => [ 'empty-cases' ],
            'When given a valid object with all keys, then forwards one case in array'
                => [ 'one-case' ],
        ];
    }

    private function subjectFor(string $scenario): ?FlattenedCanonicalData
    {
        return FlattenedCanonicalData::from($this->rawDataFor($scenario));
    }
}
