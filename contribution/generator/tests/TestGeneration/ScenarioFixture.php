<?php

namespace App\Tests\TestGeneration;

use ReflectionClass;

trait ScenarioFixture
{
    private function expectedFor(string $scenario): string
    {
        $file = $this->pathToFixtures() . '/' . $scenario . '/expected.txt';

        if (!\file_exists($file)) {
            $this->fail('Expected fixture file of scenario not found: ' . $file);
        }

        return \file_get_contents($file);
    }

    private function rawDataFor(string $scenario): mixed
    {
        $file = $this->pathToFixtures() . '/' . $scenario . '/input.json';

        if (!\file_exists($file)) {
            $this->fail('Input fixture file of scenario not found: ' . $file);
        }

        return \json_decode(
            json: \file_get_contents($file),
            flags: JSON_THROW_ON_ERROR
        );
    }

    private function pathToFixtures(): string
    {
        $classReflector = new ReflectionClass($this);

        return \dirname($classReflector->getFileName()) . '/fixtures';
    }
}
