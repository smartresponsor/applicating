import React from 'react'
export default function MeshHardening(){
  return (<section>
    <h2>Mesh Hardening</h2>
    <ul>
      <li>RBAC: X-Role = region_admin | auditor | observer</li>
      <li>mTLS/IP allowlist: включается аннотациями (см. Helm шаблон)</li>
      <li>TTL предложений: 24h</li>
      <li>Ledger audit: запись результата finalize в audit-таблицу</li>
    </ul>
  </section>)
}
