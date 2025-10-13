<?php
// phpcs:ignoreFile -- PHP Code Sniffer currently does not work with property hooks

declare(strict_types=1);

namespace App\TrackData;

final class FlattenedCanonicalData
{
    /**
     * @param array<object> $cases
     * @param string[] $comments
     */
    public function __construct(
        public string $testClassName {
            get => $this->testClassName;
            set {
                if (empty($value)) {
                    throw new \InvalidArgumentException('$testClassName cannot be empty');
                }
                $this->testClassName = $value;
            }
        },
        public string $solutionFileName {
            get => $this->solutionFileName;
            set {
                if (empty($value)) {
                    throw new \InvalidArgumentException('$solutionFileName cannot be empty');
                }
                $this->solutionFileName = $value;
            }
        },
        public string $solutionClassName {
            get => $this->solutionClassName;
            set {
                if (empty($value)) {
                    throw new \InvalidArgumentException('$solutionClassName cannot be empty');
                }
                $this->solutionClassName = $value;
            }
        },
        public array $cases,
        public string $exercise = '',
        public array $comments = [],
        public object|null $unknown = null,
    ) {
    }

    public static function from(mixed $rawData): static
    {
        if (!\is_object($rawData)) {
            throw new \InvalidArgumentException('$rawData must be an object');
        }
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
