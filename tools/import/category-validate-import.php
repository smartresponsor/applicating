<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
use App\Service\ImportValidator;
require_once __DIR__.'/../../vendor/autoload.php';
$file = $argv[1] ?? 'report/category-import-from-csv.json';
if (!is_file($file)) {
    fwrite(STDERR, "file not found\n");
    exit(1);
}
$rows = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
$validator = new ImportValidator();
$errors = $validator->validate($rows);
file_put_contents('report/category-import-errors.json', json_encode($errors, JSON_PRETTY_PRINT));
if ($errors !== []) {
    exit(2);
}
echo "ok\n";
