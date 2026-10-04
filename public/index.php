<?php

declare(strict_types=1);

use App\Applicating\Kernel;

$_SERVER['APP_RUNTIME_OPTIONS'] ??= [
    'disable_dotenv' => true,
];

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static fn (array $context): Kernel => new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
