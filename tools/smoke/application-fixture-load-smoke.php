<?php

declare(strict_types=1);

$fixture = dirname(__DIR__, 2).'/src/DataFixtures/ApplicationFixtures.php';
require_once $fixture;

fwrite(STDOUT, "Application fixture load smoke passed.\n");
