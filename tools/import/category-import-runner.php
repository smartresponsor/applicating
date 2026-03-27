<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$input = $argv[1] ?? 'report/category-import-from-csv.json';
$ts = date(DATE_ATOM);
$reportFile = 'report/category-import-runner.json';
$result = [
  'ts' => $ts,
  'input' => $input,
  'status' => 'ok',
  'errors' => [],
];
if (!is_file($input)) {
  $result['status'] = 'error';
  $result['errors'][] = 'input-not-found';
  file_put_contents($reportFile, json_encode($result, JSON_PRETTY_PRINT));
  exit(1);
}
$rows = json_decode(file_get_contents($input), true) ?? [];
$errors = [];
foreach ($rows as $i => $row) {
  if (!isset($row['slug'])) {
    $errors[] = ['row' => $i, 'error' => 'slug-missing'];
  }
}
if ($errors) {
  $result['status'] = 'error';
  $result['errors'] = $errors;
}
file_put_contents($reportFile, json_encode($result, JSON_PRETTY_PRINT));
echo $result['status']."\n";
exit($result['status'] === 'ok' ? 0 : 2);
