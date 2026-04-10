<?php

declare(strict_types=1);

$arguments = $argv ?? $_SERVER['argv'] ?? [];
array_shift($arguments);

if ([] === $arguments) {
    fwrite(STDERR, "Usage: php php82.php <command> [args...]
");
    exit(1);
}

$command = [PHP_BINARY];
foreach ($arguments as $argument) {
    $command[] = $argument;
}

$descriptorSpec = [
    0 => STDIN,
    1 => STDOUT,
    2 => STDERR,
];

$process = proc_open($command, $descriptorSpec, $pipes, dirname(__DIR__, 2));
if (!is_resource($process)) {
    fwrite(STDERR, "Unable to start delegated PHP command.
");
    exit(1);
}

exit(proc_close($process));
