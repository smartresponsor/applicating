<?php
declare(strict_types=1);
namespace App\Component\Product\AI\SelfHealing\Clients;

final class KubernetesClient
{
    public function __construct(private readonly string $namespace = 'default') {}

    /** Execute a kubectl command and return [exitCode, stdout, stderr] */
    public function kubectl(array $args): array
    {
        $cmd = ['kubectl','-n',$this->namespace, *$args];
        $descriptor = [1 => ['pipe','w'], 2 => ['pipe','w']];
        $p = proc_open($cmd, $descriptor, $pipes);
        if (!is_resource($p)) return [1,'','proc_open failed'];
        $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $code = proc_close($p);
        return [$code, $out ?: '', $err ?: ''];
    }
}
