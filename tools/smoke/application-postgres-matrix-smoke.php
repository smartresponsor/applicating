<?php

declare(strict_types=1);

$databaseUrl = $_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? null;
if (null === $databaseUrl) {
    fwrite(STDERR, "DATABASE_URL is not configured.\n");
    exit(1);
}

fwrite(STDOUT, sprintf("Application postgres matrix smoke sees DATABASE_URL=%s\n", $databaseUrl));
