<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$projectDir = dirname(__DIR__);
$autoloadFile = $projectDir.'/vendor/autoload.php';
$localEnvFile = $projectDir.'/.env.local.php';

require $autoloadFile;

if (is_file($localEnvFile)) {
    $env = include $localEnvFile;

    if (is_array($env) && (!isset($env['APP_ENV']) || ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? $env['APP_ENV']) === $env['APP_ENV'])) {
        new Dotenv('')->populate($env);

        return;
    }
}

new Dotenv()->bootEnv($projectDir.'/.env');
