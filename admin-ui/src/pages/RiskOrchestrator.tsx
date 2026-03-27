import React from 'react'
export default function RiskOrchestrator(){
  return (<section>
    <h2>Risk-Aware Policy Orchestrator</h2>
    <ul>
      <li>POST /api/policy/orchestrator/evaluate {policy, region, impact}</li>
    </ul>
  </section>)
}
