#!/usr/bin/env php
<?php

use App\UpdateCommand as Application;

(function () {
    $projectDir = \dirname(__DIR__);
    $autoloaderDir = $projectDir . '/vendor';
    $autoloaderFile = $autoloaderDir . '/autoload.php';
    if (!\is_dir($autoloaderDir) || !\is_readable($autoloaderFile)) {
        throw new \LogicException('Dependencies are missing. Try running "composer install".');
    }

    require_once $autoloaderFile;

    $application = new Application();
    $application->run();
})();
