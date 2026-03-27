<?php
declare(strict_types=1);

// scripts/backfill_product_v2.php
// Usage: php scripts/backfill_product_v2.php

require_once __DIR__ . '/../vendor/autoload.php';

$dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;port=5432;dbname=app;user=app;password=app';
$db = new PDO($dsn);

$total = 0;
$batch = 1000;
while (true) {
    $stmt = $db->query('SELECT backfill_product_read_v2(' . $batch . ') AS count');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $n = (int)($row['count'] ?? 0);
    if ($n <= 0) break;
    $total += $n;
    fwrite(STDERR, "Backfilled: {$n} (total {$total})\n");
    usleep(100000); // 100ms pause to reduce pressure
}

fwrite(STDERR, "Done. Total backfilled: {$total}\n");
