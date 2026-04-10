<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$controllers = applicating_scan_php_files(['src/Controller']);
$routes = [];
foreach ($controllers as $controller) {
    foreach (applicating_parse_routes_from_controller($controller) as $route) {
        $routes[] = $route + ['controller' => str_replace(applicating_root() . '/', '', $controller)];
    }
}

$payload = [
    'tool' => 'applicating-route-inventory',
    'status' => 'complete',
    'routes' => $routes,
    'count' => count($routes),
];

applicating_write_json('report/inspection/applicating-route-inventory.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
