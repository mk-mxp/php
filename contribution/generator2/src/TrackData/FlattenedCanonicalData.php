<?php

declare(strict_types=1);

namespace App\TrackData;

final class FlattenedCanonicalData
{
    /**
     * @param array<object> $cases
     * @param string[] $comments
     */
    public function __construct(
        public string $testClassName,
        public string $solutionFileName,
        public string $solutionClassName,
        public array $cases,
        public string $exercise = '',
        public array $comments = [],
        public object|null $unknown = null,
    ) {
    }

    public static function from(mixed $rawData): static
    {
        /** @var \stdClass $rawData */

        $requiredProperties = [
            'testClassName',
            'solutionFileName',
            'solutionClassName',
            'cases',
        ];
        $requiredData = [];
        foreach ($requiredProperties as $requiredProperty) {
            $requiredData[$requiredProperty] = $rawData->{$requiredProperty};
            unset($rawData->{$requiredProperty});
        }
        /** @var array{testClassName: string, solutionFileName: string, solutionClassName: string, cases: array<object>} $requiredData */

        /** @var string[] $comments */
        $comments = $rawData->comments ?? [];
        unset($rawData->comments);

        /** @var string $exercise */
        $exercise = $rawData->exercise ?? '';
        unset($rawData->exercise);

        return new static(
            ...$requiredData,
            exercise: $exercise,
            comments: $comments,
            unknown: empty(\get_object_vars($rawData)) ? null : $rawData,
        );
    }
}
