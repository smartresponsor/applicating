<?php
declare(strict_types=1);
namespace App\Component\Product\AI\PolicyEngine;

final class PolicyEngine
{
    /** @param array<int,array{name:string,condition:string,action:string}> $policies */
    public function __construct(private array $policies) {}

    /** @param array<string,mixed> $ctx */
    public function decide(array $ctx): array
    {
        foreach ($this->policies as $p) {
            if ($this->evalCondition($p['condition'], $ctx)) {
                return ['policy'=>$p['name'],'action'=>$p['action']];
            }
        }
        return ['policy'=>null,'action'=>'no-op'];
    }

    /** very small expression evaluator: supports 'and','or','>','<','==','!=' on numeric/string ctx keys */
    private function evalCondition(string $expr, array $ctx): bool
    {
        // replace keys with values
        $e = $expr;
        foreach ($ctx as $k=>$v) {
            $val = is_numeric($v) ? (string)$v : (''' . addslashes((string)$v) . ''');
            $e = preg_replace('/\b'.preg_quote((string)$k,'/').'\b/', $val, $e);
        }
        // normalize operators
        $e = str_ireplace([' and ',' or '], [' && ', ' || '], $e);
        // safety: allow only simple chars
        if (preg_match('/[^\w\s\&\|\!\=\<\>\'\.\-]/', $e)) return false;
        try { return (bool) eval('return ('. $e . ');'); } catch (\Throwable $t) { return false; }
    }
}
