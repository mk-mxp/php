<?php

declare(strict_types=1);

namespace App\TrackData;

class FlattenedCanonicalData
{
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

    public static function from(mixed $rawData): ?static
    {
        if (!\is_object($rawData)) {
            return null;
        }

        $requiredProperties = [
            'testClassName',
            'solutionFileName',
            'solutionClassName',
            'cases',
        ];
        $actualProperties = \array_keys(\get_object_vars($rawData));
        $requiredData = [];
        foreach ($requiredProperties as $requiredProperty) {
            if (!\in_array($requiredProperty, $actualProperties)) {
                return null;
            }
            $requiredData[$requiredProperty] = $rawData->{$requiredProperty};
            unset($rawData->{$requiredProperty});
        }

        $comments = $rawData->comments ?? [];
        unset($rawData->comments);

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
