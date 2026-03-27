<?php

declare(strict_types=1);

namespace App\Component\Product\Legal\Controller;

use App\Component\Product\Legal\ContractTemplateEngine;
use App\Component\Product\Legal\Validator\AssuranceValidator;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class LegalAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly ContractTemplateEngine $tmpl,
        private readonly AssuranceValidator $assurance,
    ) {
    }

    #[Route('/api/legal/template', methods: ['POST'])]
    public function template(Request $req): JsonResponse
    {
        $name = (string) $req->get('name', 'dpa');
        $ctx = json_decode((string) $req->get('context', '{}'), true) ?: $this->tmpl->defaultContext();
        $tpl = $this->db->fetchOne('SELECT body FROM legal_templates WHERE name=?', [$name]);
        if (!$tpl) {
            return new JsonResponse(['ok' => false, 'error' => 'template_not_found'], 404);
        }
        $html = $this->tmpl->render((string) $tpl, $ctx);
        $this->db->insert('legal_contracts', [
            'ts' => gmdate('c'), 'name' => $name, 'body' => $html, 'context' => json_encode($ctx),
        ]);

        return new JsonResponse(['ok' => true, 'html' => $html]);
    }

    #[Route('/api/legal/assurance/check', methods: ['POST'])]
    public function assuranceCheck(Request $req): JsonResponse
    {
        $rules = json_decode((string) $req->get('rules', '{}'), true) ?: ['minRetention' => 30, 'maxDpia' => 0.7];
        $res = $this->assurance->check($rules);
        $this->db->insert('legal_assurance_checks', [
            'ts' => gmdate('c'),
            'rules' => json_encode($rules),
            'result' => json_encode($res),
        ]);

        return new JsonResponse(['ok' => true, 'result' => $res]);
    }

    #[Route('/api/legal/dpa', methods: ['POST'])]
    public function dpa(Request $req): JsonResponse
    {
        $ctx = $this->tmpl->defaultContext();
        $tpl = $this->db->fetchOne("SELECT body FROM legal_templates WHERE name='dpa'");
        if (!$tpl) {
            return new JsonResponse(['ok' => false, 'error' => 'template_not_found'], 404);
        }
        $html = $this->tmpl->render((string) $tpl, $ctx);
        $this->db->insert('legal_contracts', ['ts' => gmdate('c'), 'name' => 'dpa', 'body' => $html, 'context' => json_encode($ctx)]);

        return new JsonResponse(['ok' => true, 'html' => $html, 'context' => $ctx]);
    }
}
