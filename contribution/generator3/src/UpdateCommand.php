<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

use function assert;
use function is_string;
use function realpath;

class UpdateCommand extends SingleCommandApplication
{
    private const EXERCISES_PATH = '/exercises/practice/';

    public function __construct()
    {
        parent::__construct('Exercism PHP Test Generator for Updates');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setVersion('1.0.0');
        $this->addArgument('project-dir', InputArgument::REQUIRED, 'Path to project with the exercises in ' . self::EXERCISES_PATH . '.');
        $this->addArgument('exercise-slug', InputArgument::REQUIRED, 'Slug of the exercise in ' . self::EXERCISES_PATH . '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $input->getArgument('project-dir');
        $exerciseSlug = $input->getArgument('exercise-slug');
        assert(is_string($projectDir), 'project-dir must be a string');
        assert(is_string($exerciseSlug), 'exercise-slug must be a string');

        $logger = new ConsoleLogger($output, [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        ]);

        $exercisePath = $projectDir . self::EXERCISES_PATH . $exerciseSlug;

        if (!\is_dir($exercisePath) || !\is_writable($exercisePath)) {
            $logger->error('Cannot update exercise in path: ' . $exercisePath);
            return self::FAILURE;
        }

        $exercisePath = realpath($exercisePath);
        $logger->notice('Updating exercise in path: ' . $exercisePath);

        return self::SUCCESS;
    }
}
