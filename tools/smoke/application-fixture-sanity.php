<?php

declare(strict_types=1);

$fixture = dirname(__DIR__, 2).'/src/DataFixtures/ApplicationFixtures.php';
if (!file_exists($fixture)) {
    fwrite(STDERR, "ApplicationFixtures missing.\n");
    exit(1);
}

fwrite(STDOUT, "Application fixture sanity passed.\n");
