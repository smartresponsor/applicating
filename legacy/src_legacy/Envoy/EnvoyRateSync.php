<?php

declare(strict_types=1);

namespace App\Component\Product\Envoy;

final class EnvoyRateSync
{
    /** Экспорт лимитов в Envoy/Redis (демо: запись в файл). */
    public function export(array $limits): void
    {
        file_put_contents('/tmp/envoy_ratelimit_export.json', json_encode($limits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
