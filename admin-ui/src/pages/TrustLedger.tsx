import React from 'react'
export default function TrustLedger(){
  return (<section>
    <h2>Trust Ledger</h2>
    <ul>
      <li>GET  /api/trust-ledger/head</li>
      <li>POST /api/trust-ledger/append {payload, signature}</li>
      <li>GET  /api/trust-ledger/verify?depth=...</li>
    </ul>
  </section>)
}
