<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\Exporter;

final class PrometheusExporter
{
    /** @param array<int,array{name:string,labels:array<string,string>,value:float}> $rows */
    public function render(array $rows): string
    {
        $out = [];
        foreach ($rows as $r) {
            $labels = [];
            foreach ($r['labels'] as $k => $v) {
                $labels[] = $k.'="'.addslashes($v).'"';
            }
            $out[] = $r['name'].'{'.implode(',', $labels).'} '.((string) $r['value']);
        }

        return implode("\n", $out)."\n";
    }
}
