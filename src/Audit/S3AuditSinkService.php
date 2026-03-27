<?php

declare(strict_types=1);

namespace App\Component\Product\Audit;

use Doctrine\DBAL\Connection;

final class S3AuditSinkService
{
    public function __construct(private readonly Connection $db, private readonly string $bucket, private readonly string $prefix = 'audit/')
    {
    }

    /** @return string path to gz file */
    public function dumpDay(\DateTimeImmutable $day): string
    {
        $from = $day->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $to = $day->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $rows = $this->db->fetchAllAssociative('SELECT * FROM audit_log WHERE occurred_at BETWEEN :f AND :t ORDER BY occurred_at', ['f' => $from, 't' => $to]);
        $tmp = sys_get_temp_dir().'/audit_'.$day->format('Ymd').'.ndjson';
        $gz = $tmp.'.gz';
        $fh = fopen($tmp, 'w');
        foreach ($rows as $r) {
            fwrite($fh, json_encode($r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
        }
        fclose($fh);
        $data = file_get_contents($tmp);
        $gzdata = gzencode($data, 9);
        file_put_contents($gz, $gzdata);
        @unlink($tmp);

        return $gz;
    }

    public function uploadWithCli(string $gzPath): bool
    {
        $key = $this->prefix.basename($gzPath);
        $cmd = sprintf('%s "%s" "%s"', getenv('S3_SYNC_CMD') ?: 'aws s3 cp', $gzPath, $this->bucket.'/' + $key);
        // fallback: print instruction
        if (PHP_OS_FAMILY === 'Windows') {
            return false;
        }

        return 0 === system($cmd);
    }
}
