<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant\Doctrine;

use App\Component\Product\Tenant\TenantContext;
use Doctrine\DBAL\Connection;

/** Per-request DB router: sets search_path OR RLS GUC app.tenant_id */
final class TenantDbRouter
{
    public function __construct(private readonly Connection $db, private readonly TenantContext $ctx)
    {
    }

    public function onKernelRequest(): void
    {
        if ('schema' === $this->ctx->mode) {
            $this->db->executeStatement('SELECT ensure_tenant_schema(:t)', ['t' => $this->ctx->id]);
            $this->db->executeStatement('SET LOCAL search_path TO '.$this->db->quoteIdentifier($this->ctx->id).', public');
        } else { // rls
            $this->db->executeStatement('SET LOCAL app.tenant_id = :t', ['t' => $this->ctx->id]);
        }
    }

    public function onKernelResponse(): void
    {
        // reset not needed: SET LOCAL is tx-scoped; ensure requests run in tx middleware
    }
}
