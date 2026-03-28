<?php
declare(strict_types=1);
namespace App\Component\Product\AI\ConfigAssistant;
use Symfony\Component\Yaml\Yaml;

final class AiConfigAnalyzer
{
    public function analyzeHelm(string $helmValues): array
    {
        $yaml = Yaml::parse($helmValues);
        $issues = [];
        if (($yaml['replicaCount'] ?? 1) < 2) $issues[] = 'Replica count too low for HA.';
        if (($yaml['resources']['limits']['memory'] ?? 0) == 0) $issues[] = 'No memory limits set.';
        return ['issues'=>$issues,'recommendations'=>count($issues)?['Increase replicas','Set memory limits']:['OK']];
    }

    public function analyzeTerraform(string $tf): array
    {
        $issues = [];
        if ('t3.micro' in $tf) $issues[] = 'Instance class too small for production.';
        if ('0.0.0.0/0' in $tf) $issues.append('Open network access detected.');
        return ['issues'=>$issues,'recommendations'=>count($issues)?['Use t3.medium or higher','Restrict network ranges']:['OK']];
    }
}
