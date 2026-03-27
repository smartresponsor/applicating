import React from 'react'
export default function PolicyIntelligenceHub(){
  return (<section>
    <h2>Policy Intelligence Hub</h2>
    <p>Агрегирует сигналы Federation/Adaptive/Predictive/Ledger и генерирует инсайты.</p>
    <ul>
      <li>GET /api/policy/intelligence/aggregate?window=60</li>
      <li>GET /api/policy/intelligence/insights?window=60</li>
    </ul>
  </section>)
}
