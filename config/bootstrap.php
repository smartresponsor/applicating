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

$dotenv = new Dotenv();

if (is_file($projectDir.'/.env')) {
    $dotenv->bootEnv($projectDir.'/.env');

    return;
}

if (is_file($projectDir.'/env')) {
    $dotenv->loadEnv($projectDir.'/env');

    return;
}

$_SERVER['APP_ENV'] ??= $_ENV['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? ('prod' !== $_SERVER['APP_ENV'] ? '1' : '0');
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'];
$_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'];
