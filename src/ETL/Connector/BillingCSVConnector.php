<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Connector;

final class BillingCSVConnector
{
    /** @return array<int,array<string,string>> */
    public function parse(string $csvFile): array
    {
        $rows = [];
        if (($h = fopen($csvFile, 'r')) !== false) {
            $header = fgetcsv($h, 0, ',');
            if (!is_array($header)) {
                fclose($h);

                return [];
            }
            while (($r = fgetcsv($h, 0, ',')) !== false) {
                $rows[] = array_combine($header, $r);
            }
            fclose($h);
        }

        return $rows;
    }
}
