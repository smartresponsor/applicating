<?php

declare(strict_types=1);

namespace App\Component\Product\AI\SelfHealing\Orchestrator;

use App\Component\Product\AI\SelfHealing\Clients\KubernetesClient;
use Doctrine\DBAL\Connection;

final class ActionOrchestrator
{
    public function __construct(private readonly Connection $db, private readonly KubernetesClient $k8s)
    {
    }

    /** @param array<string,mixed> $ctx */
    public function apply(string $action, array $ctx): array
    {
        $result = ['ok' => false, 'action' => $action, 'ctx' => $ctx, 'stdout' => '', 'stderr' => ''];
        try {
            switch ($action) {
                case 'restart':
                    [$code,$out,$err] = $this->k8s->kubectl(['rollout', 'restart', 'deployment/'.($ctx['deployment'] ?? 'app')]);
                    $result = ['ok' => 0 === $code, 'action' => $action, 'stdout' => $out, 'stderr' => $err];
                    break;

                case 'scale-up':
                case 'scale-down':
                    $replicas = (int) ($ctx['replicas'] ?? 2);
                    [$code,$out,$err] = $this->k8s->kubectl(['scale', 'deployment', $ctx['deployment'] ?? 'app', '--replicas='.$replicas]);
                    $result = ['ok' => 0 === $code, 'action' => 'scale', 'stdout' => $out, 'stderr' => $err, 'replicas' => $replicas];
                    break;

                case 'hpa-patch':
                    $target = (string) ($ctx['hpa'] ?? 'app-hpa');
                    $min = (int) ($ctx['min'] ?? 2);
                    $max = (int) ($ctx['max'] ?? 6);
                    $cpu = (int) ($ctx['cpu'] ?? 70);
                    $patch = json_encode(['spec' => ['minReplicas' => $min, 'maxReplicas' => $max, 'targetCPUUtilizationPercentage' => $cpu]]);
                    [$code,$out,$err] = $this->k8s->kubectl(['patch', 'hpa', $target, '-p', $patch, '--type=merge']);
                    $result = ['ok' => 0 === $code, 'action' => $action, 'stdout' => $out, 'stderr' => $err, 'patch' => $patch];
                    break;

                case 'helm-rollback':
                    $release = (string) ($ctx['release'] ?? 'smartresponsor');
                    $rev = (string) ($ctx['revision'] ?? 'LAST_SUCCESS');
                    // use shell helm via kubectl plugin if available
                    [$code,$out,$err] = $this->k8s->kubectl(['exec', 'deploy/'.($ctx['helmPod'] ?? 'helm-runner'), '--', 'helm', 'rollback', $release, $rev, '--wait']);
                    $result = ['ok' => 0 === $code, 'action' => $action, 'stdout' => $out, 'stderr' => $err, 'release' => $release, 'revision' => $rev];
                    break;

                case 'circuit-breaker-on':
                    // add label to deployment to force traffic cut by service mesh (example)
                    $dep = (string) ($ctx['deployment'] ?? 'app');
                    [$code,$out,$err] = $this->k8s->kubectl(['label', 'deployment', $dep, 'traffic=blocked', '--overwrite=true']);
                    $result = ['ok' => 0 === $code, 'action' => $action, 'stdout' => $out, 'stderr' => $err];
                    break;

                case 'circuit-breaker-off':
                    $dep = (string) ($ctx['deployment'] ?? 'app');
                    [$code,$out,$err] = $this->k8s->kubectl(['label', 'deployment', $dep, 'traffic-']);
                    $result = ['ok' => 0 === $code, 'action' => $action, 'stdout' => $out, 'stderr' => $err];
                    break;

                default:
                    $result['stderr'] = 'unknown action: '.$action;
            }
        } catch (\Throwable $e) {
            $result['stderr'] = $e->getMessage();
        }

        // audit
        $this->db->insert('healing_events', [
            'alertname' => (string) ($ctx['alertname'] ?? 'manual'),
            'summary' => (string) ($ctx['reason'] ?? ''),
            'action' => $action,
            'outcome' => $result['ok'] ? 'success' : 'failure',
            'details' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $result;
    }
}
