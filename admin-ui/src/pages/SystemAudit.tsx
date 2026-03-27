import React from 'react'
export default function SystemAudit(){
  return (<section>
    <h2>System Integrity & Audit</h2>
    <ul>
      <li>POST /api/audit/log {source, event, payload}</li>
      <li>POST /api/audit/append {type, payload}</li>
      <li>GET  /api/audit/check?depth=1000</li>
    </ul>
  </section>)
}
