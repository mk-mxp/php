<?php

namespace App\Tests\UpdateTests;

use App\Tests\UpdateTests\ScenarioFixture;
use App\TrackData\FlattenedCanonicalData;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use TypeError;

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
    #[DataProvider('unhappyScenarios')]
    public function testUnhappyScenario(
        string $scenario,
        string $expectedException,
    ): void {
        $this->expectException($expectedException);

        // Silence PHP warnings about unset properties
        @$this->subjectFor($scenario);
    }

    /** @return array<string, string[]> */
    public static function unhappyScenarios(): array
    {
        return [
            'Throws when given no object'
                => [ 'no-object', InvalidArgumentException::class ],
            'Throws when given an object with no `testClassName` key'
                => [ 'no-test-class-name', TypeError::class ],
            'Throws when given an object with no `solutionFileName` key'
                => [ 'no-solution-file-name', TypeError::class ],
            'Throws when given an object with no `cases` key'
                => [ 'no-cases', TypeError::class ],
        ];
    }

    #[Test]
    #[TestDox('$_dataName')]
    #[DataProvider('happyScenarios')]
    public function testHappyScenario(
        string $scenario,
    ): void {
        $subject = $this->subjectFor($scenario);

        $this->assertObjectEqualsJsonOutputFixture(
            $scenario,
            $subject,
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

    private function subjectFor(string $scenario): FlattenedCanonicalData
    {
        return FlattenedCanonicalData::from($this->rawDataFor($scenario));
    }
}
