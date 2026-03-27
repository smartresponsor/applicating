<?php

declare(strict_types=1);

namespace App\Component\Product\Cloud\Autoscale;

final class K8sClient
{
    public function scale(string $deployment, int $replicas): void
    {
        // демо: тут должен быть вызов kubectl/Helm API
        file_put_contents('/tmp/k8s_scale.log', sprintf("%s %d\n", $deployment, $replicas), FILE_APPEND);
    }
}
