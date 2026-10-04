<?php

declare(strict_types=1);

$publicDirectory = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? $requestPath : '/';
$staticFile = $publicDirectory.str_replace('/', DIRECTORY_SEPARATOR, $requestPath);

if ('/' !== $requestPath && is_file($staticFile)) {
    return false;
}

$frontController = $publicDirectory.DIRECTORY_SEPARATOR.'index.php';
$_SERVER['SCRIPT_FILENAME'] = $frontController;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require $frontController;
