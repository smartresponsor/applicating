<?php

declare(strict_types=1);

use App\Kernel;

require dirname(__DIR__, 2).'/config/bootstrap.php';

$kernel = new Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer();
$container->get(App\ServiceInterface\ApplicationLifecycleServiceInterface::class);

fwrite(STDOUT, "Application container boot smoke passed.\n");
