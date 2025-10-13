<?php

namespace App\Tests\UpdateTests;

use ReflectionClass;

trait ScenarioFixture
{
    private function rawDataFor(string $scenario): mixed
    {
        $file = $this->pathToScenarioFixtures($scenario) . '/input.json';

        if (!\file_exists($file)) {
            $this->fail('Input fixture file of scenario not found: ' . $file);
        }

        return \json_decode(
            json: \file_get_contents($file) ?: '',
            flags: JSON_THROW_ON_ERROR
        );
    }

    private function assertObjectEqualsJsonOutputFixture(
        string $scenario,
        object $actual,
        string $message = '',
    ): void {
        $file = $this->pathToScenarioFixtures($scenario) . '/output.json';

        if (!\file_exists($file)) {
            $this->fail('Output fixture file of scenario not found: ' . $file);
        }

        $expected = \file_get_contents($file) ?: '';

        $this->assertJson(
            $expected,
            'Output fixture file of scenario is no valid JSON: ' . $file
        );

        $this->assertSame(
            \trim($expected),
            \json_encode($actual, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR),
            $message
        );
    }

    private function pathToScenarioFixtures(string $scenario): string
    {
        return $this->pathToFixtures() . '/' . $scenario;
    }

    private function pathToFixtures(): string
    {
        $classReflector = new ReflectionClass($this);

        return \dirname($classReflector->getFileName() ?: '') . '/fixtures';
    }
}
