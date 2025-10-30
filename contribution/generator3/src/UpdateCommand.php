<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Throwable;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

use function array_filter;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_file;
use function is_object;
use function is_string;
use function is_readable;
use function is_writable;
use function json_decode;
use function preg_replace;
use function realpath;
use function str_replace;
use function ucwords;

use const ARRAY_FILTER_USE_BOTH;
use const JSON_THROW_ON_ERROR;

class UpdateCommand extends SingleCommandApplication
{
    private const EXERCISES_PATH = '/exercises/practice/';
    private const TEMPLATE_PATH = '/.meta/tests.php.twig';
    private const TESTS_TOML_PATH = '/.meta/tests.toml';

    public function __construct()
    {
        parent::__construct('Exercism PHP Test Generator for Updates');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setVersion('1.0.0');
        $this->addArgument(
            'exercise-slug',
            InputArgument::REQUIRED,
            'Slug of the exercise in ' . self::EXERCISES_PATH . '.',
        );
        $this->addArgument(
            'project-dir',
            InputArgument::OPTIONAL,
            'Path to project with the exercises in ' . self::EXERCISES_PATH . '.',
            './',
        );
        $this->addArgument(
            'canonical-data',
            InputArgument::OPTIONAL,
            'Path to canonical data for the exercise <exercise-slug>.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output, [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        ]);

        try {
            $projectDir = $this->usableProjectDir(
                $input->getArgument('project-dir'),
            );
            $exerciseSlug = $this->usableExerciseSlug(
                $input->getArgument('exercise-slug'),
            );
            $exercisePath = $this->usableExercisePath(
                $projectDir,
                $exerciseSlug,
            );
            $twigTemplateFile = $this->usableTwigTemplateFile(
                $exercisePath,
            );
            $testsTomlFile = $this->usableTestsTomlFile(
                $exercisePath,
            );
            $canonicalDataFile = $this->usableCanonicalDataFile(
                $input->getArgument('canonical-data'),
                $exerciseSlug,
            );

            $logger->notice('Updating exercise in path: ' . $exercisePath);

            $canonicalData = $this->canonicalData(
                $canonicalDataFile,
            );
            $testsToml = $this->testsTomlData(
                $testsTomlFile,
            );

            $this->mergeTestsTomlIntoCanonicalData(
                $canonicalData,
                $testsToml,
            );

            $renderedTests = $this->renderTemplate(
                $twigTemplateFile,
                $canonicalData,
            );
            $exerciseTestFile = $exercisePath . '/' . $this->pascalCase($exerciseSlug) . 'Test.php';
            file_put_contents(
                $exerciseTestFile,
                $renderedTests,
            );
        } catch (Throwable $exception) {
            $logger->error($exception);
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function usableProjectDir(mixed $rawProjectDir): string
    {
        if (!is_string($rawProjectDir)) {
            throw new InvalidArgumentException('project-dir must be string');
        }

        $projectDir = realpath($rawProjectDir);

        if (
            $projectDir === false
            || !is_dir($projectDir)
            || !is_readable($projectDir)
        ) {
            throw new InvalidArgumentException('Cannot use project-dir "' . $rawProjectDir . '"');
        }

        return $projectDir;
    }

    protected function usableExerciseSlug(mixed $rawExerciseSlug): string
    {
        if (!is_string($rawExerciseSlug)) {
            throw new InvalidArgumentException('exercise-slug must be string');
        }

        // TODO: RegEx for slug?

        return $rawExerciseSlug;
    }

    protected function usableExercisePath(string $projectDir, string $exerciseSlug): string
    {
        $rawExercisePath = $projectDir . self::EXERCISES_PATH . $exerciseSlug;
        $exercisePath = realpath($rawExercisePath);

        if (
            $exercisePath === false
            || !is_dir($exercisePath)
            || !is_writable($exercisePath)
        ) {
            throw new InvalidArgumentException('Cannot update exercise in "' . $rawExercisePath . '"');
        }

        return $exercisePath;
    }

    protected function usableTwigTemplateFile(string $exercisePath): string
    {
        return $this->readableFile($exercisePath, self::TEMPLATE_PATH);
    }

    protected function usableTestsTomlFile(string $exercisePath): string
    {
        return $this->readableFile($exercisePath, self::TESTS_TOML_PATH);
    }

    protected function readableFile(string $basePath, string $pathToFile): string
    {
        $file = $basePath . $pathToFile;

        if (!is_file($file) || !is_readable($file)) {
            throw new InvalidArgumentException('No readable file "' . $file . '"');
        }

        return $file;
    }

    protected function usableCanonicalDataFile(mixed $rawCanonicalData, string $exerciseSlug): string
    {
        $trackRoot = (string)realpath(__DIR__ . '/../../../');
        $canonicalData = $rawCanonicalData
            ?? new Configlet($trackRoot)->pathToCanonicalData($exerciseSlug)
            ;

        if (!is_string($canonicalData)) {
            throw new InvalidArgumentException('canonical-data must be string');
        }

        if (!is_file($canonicalData) || !is_readable($canonicalData)) {
            throw new InvalidArgumentException('No readable canonical data "' . $canonicalData . '"');
        }

        return $canonicalData;
    }

    protected function canonicalData(string $canonicalData): object
    {
        return (object)json_decode((string)file_get_contents($canonicalData), flags: JSON_THROW_ON_ERROR);
    }

    /** @return array<string, array{}|bool|int|string> */
    protected function testsTomlData(string $file): array
    {
        return TomlParser::parse((string)file_get_contents($file));
    }

    /**
     * Remove tests that are `include = false` in tests.toml and use description
     * from tests.toml (this is a combined description for nested cases)
     *
     * @param object $canonicalData
     * @param array<string, array<string, array{}|bool|int|string>|bool|int|string> $testsTomlData
     */
    protected function mergeTestsTomlIntoCanonicalData(
        object $canonicalData,
        array $testsTomlData,
    ): void {
        $excludedTests = array_filter(
            $testsTomlData,
            static function (mixed $props, string $key): bool {
                if (!is_array($props)) {
                    throw new InvalidArgumentException(
                        'Unusable test case in tests.toml "' . $key . '"'
                    );
                }

                return isset($props['include']) && ($props['include'] === false);
            },
            ARRAY_FILTER_USE_BOTH,
        );

        if (
            !(
                isset($canonicalData->cases)
                && is_array($canonicalData->cases)
            )
        ) {
            throw new InvalidArgumentException(
                'No test cases in canonical data.'
            );
        }

        foreach ($canonicalData->cases as $key => $case) {
            if (
                !(
                    is_object($case)
                    && isset($case->uuid)
                    && is_string($case->uuid)
                    && isset($case->description)
                )
            ) {
                throw new InvalidArgumentException(
                    'Unusable test case in canonical data "' . $key . '"'
                );
            }

            if (isset($excludedTests[$case->uuid])) {
                unset($canonicalData->cases[$key]);
                continue;
            }

            if (
                !(
                    isset($testsTomlData[$case->uuid])
                    && is_array($testsTomlData[$case->uuid])
                )
            ) {
                throw new InvalidArgumentException(
                    'Missing test in tests.toml "' . $case->uuid . '"'
                );
            }

            if (!isset($testsTomlData[$case->uuid]['description'])) {
                throw new InvalidArgumentException(
                    'Missing test description in tests.toml "' . $case->uuid . '"'
                );
            }

            $case->description = $testsTomlData[$case->uuid]['description'];
        }
    }

    protected function renderTemplate(string $twigTemplate, object $canonicalData): string
    {
        $twigLoader = new ArrayLoader();
        $twigEnvironment = new Environment(
            $twigLoader,
            [
                'strict_variables' => true,
                'autoescape' => false,
                'use_yield' => true,
                'debug' => true,
            ]
        );
        $twigEnvironment->addExtension(new DebugExtension());
        $twigEnvironment->addFunction(new TwigFunction(
            'testfn',
            fn (string $label): string => 'test' . $this->pascalCase($label),
        ));
        $template = (string)file_get_contents($twigTemplate);

        return $twigEnvironment
            ->createTemplate($template, $twigTemplate)
            ->render(['data' => $canonicalData])
            ;
    }

    protected function pascalCase(string $text): string
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                preg_replace('/[^a-zA-Z0-9]/', ' ', $text) ?? '',
            ),
        );
    }
}
