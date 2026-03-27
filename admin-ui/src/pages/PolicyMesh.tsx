import React from 'react'
export default function PolicyMesh(){
  return (<section>
    <h2>Cognitive Policy Mesh</h2>
    <ul>
      <li>POST /api/policy/mesh/propose {topic, payload(json), region}</li>
      <li>POST /api/policy/mesh/vote {proposal_id, region, vote=approve|reject}</li>
      <li>POST /api/policy/mesh/finalize {proposal_id, quorum}</li>
      <li>GET  /api/policy/mesh/map</li>
      <li>GET  /api/policy/mesh/agent/status</li>
    </ul>
  </section>)
}
