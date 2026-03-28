<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';

if (!file_exists(dirname(__DIR__, 2).'/public/index.php')) {
    fwrite(STDERR, "public/index.php missing.\n");
    exit(1);
}

fwrite(STDOUT, "Application runtime smoke passed.\n");
