import React from 'react'
export default function PredictiveGovernance(){
  return (<section>
    <h2>Predictive Governance</h2>
    <ul>
      <li>POST /api/policy/predictive/whatif {policy, effects(json), runs}</li>
      <li>POST /api/policy/predictive/mlscore {trust, discount, rate_limit}</li>
    </ul>
  </section>)
}
