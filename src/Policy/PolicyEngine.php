<?php

declare(strict_types=1);

namespace App\Component\Product\Policy;

final class PolicyEngine
{
    /** Очень упрощённый YAML/JSON движок: when/allow/effect */
    public function evaluate(array $policies, array $ctx): array
    {
        $effects = [];
        $allowed = true;
        foreach ($policies as $p) {
            $when = $p['when'] ?? null;
            if ($when && !$this->evalExpr($when, $ctx)) {
                continue;
            }
            if (isset($p['allow'])) {
                $allowed = $allowed && $this->evalExpr($p['allow'], $ctx);
            }
            if (isset($p['deny']) && $this->evalExpr($p['deny'], $ctx)) {
                $allowed = false;
            }
            if (isset($p['effect'])) {
                $effects[] = $p['effect'];
            }
        }

        return ['allow' => $allowed, 'effects' => $effects];
    }

    private function evalExpr(string $expr, array $ctx): bool
    {
        // Простейший парсер: поддержим ==, !=, >, <, >=, <=, &&, ||
        $e = $expr;
        // Замены контекста вида tenant.plan -> значения
        $e = preg_replace_callback('/([a-zA-Z_][a-zA-Z0-9_\.]+)/', function ($m) use ($ctx) {
            $path = explode('.', $m[1]);
            $val = $ctx;
            foreach ($path as $k) {
                if (!is_array($val) || !array_key_exists($k, $val)) {
                    return $m[0];
                }
                $val = $val[$k];
            }
            if (is_string($val)) {
                return "'".str_replace("'", "\'", $val)."'";
            }
            if (is_bool($val)) {
                return $val ? 'true' : 'false';
            }
            if (is_numeric($val)) {
                return (string) $val;
            }

            return 'null';
        }, $e);
        $e = str_replace(['&&', '||'], [' and ', ' or '], $e);
        // Безопасный eval-подобный разбор (упрощённо)
        try {
            // Используем create_function-заглушку: здесь просто возвращаем сравнение через eval-like
            // Заменим операторы на PHP
            $e = str_replace([' and ', ' or '], [' && ', ' || '], $e);
            // Белый список символов
            if (preg_match('/[^0-9a-zA-Z_\s\(\)\=\!\>\<\&\|\'\.\-]/', $e)) {
                return false;
            }

            return (bool) eval('return ('.$e.');');
        } catch (\Throwable $t) {
            return false;
        }
    }
}
