import React from 'react'
export default function GovernanceLayer(){
  return (<section>
    <h2>Autonomous Governance Layer</h2>
    <ul>
      <li>POST /api/policy/governance/evaluate {policy, region}</li>
      <li>POST /api/policy/governance/enforce {policy, meta}</li>
    </ul>
  </section>)
}
