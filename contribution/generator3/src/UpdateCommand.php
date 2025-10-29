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
use Twig\Loader\ArrayLoader;

use function is_dir;
use function is_file;
use function is_string;
use function is_readable;
use function is_writable;
use function realpath;

class UpdateCommand extends SingleCommandApplication
{
    private const EXERCISES_PATH = '/exercises/practice/';
    private const TEMPLATE_PATH = '/.meta/tests.php.twig';

    public function __construct()
    {
        parent::__construct('Exercism PHP Test Generator for Updates');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setVersion('1.0.0');
        $this->addArgument(
            'project-dir',
            InputArgument::REQUIRED,
            'Path to project with the exercises in ' . self::EXERCISES_PATH . '.'
        );
        $this->addArgument(
            'exercise-slug',
            InputArgument::REQUIRED,
            'Slug of the exercise in ' . self::EXERCISES_PATH . '.'
        );
        $this->addArgument(
            'canonical-data',
            InputArgument::OPTIONAL,
            'Path to canonical data for the exercise <exercise-slug>.'
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
            $twigTemplate = $this->usableTwigTemplate(
                $exercisePath,
            );
            $canonicalData = $this->usableCanonicalData(
                $input->getArgument('canonical-data'),
                $exerciseSlug,
            );

            $logger->notice('Updating exercise in path: ' . $exercisePath);

            $renderedTests = $this->renderTemplate(
                $twigTemplate,
                (object)\json_decode((string)\file_get_contents($canonicalData), flags: \JSON_THROW_ON_ERROR)
            );

            var_dump($renderedTests);
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
            || !\is_dir($projectDir)
            || !\is_readable($projectDir)
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
            || !\is_dir($exercisePath)
            || !\is_writable($exercisePath)
        ) {
            throw new InvalidArgumentException('Cannot update exercise in "' . $rawExercisePath . '"');
        }

        return $exercisePath;
    }

    protected function usableTwigTemplate(string $exercisePath): string
    {
        $twigTemplate = $exercisePath . self::TEMPLATE_PATH;

        if (!\is_file($twigTemplate) || !\is_readable($twigTemplate)) {
            throw new InvalidArgumentException('No readable TWIG template "' . $twigTemplate . '"');
        }

        return $twigTemplate;
    }

    protected function usableCanonicalData(mixed $rawCanonicalData, string $exerciseSlug): string
    {
        $trackRoot = (string)realpath(__DIR__ . '/../../../');
        $canonicalData = $rawCanonicalData
            ?? new Configlet($trackRoot)->pathToCanonicalData($exerciseSlug)
            ;

        if (!is_string($canonicalData)) {
            throw new InvalidArgumentException('canonical-data must be string');
        }

        if (!\is_file($canonicalData) || !\is_readable($canonicalData)) {
            throw new InvalidArgumentException('No readable canonical data "' . $canonicalData . '"');
        }

        return $canonicalData;
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
                // 'debug' => true,
            ]
        );
        $template = (string)\file_get_contents($twigTemplate);

        return $twigEnvironment
            ->createTemplate($template, $twigTemplate)
            ->render(['data' => $canonicalData])
            ;
    }
}
