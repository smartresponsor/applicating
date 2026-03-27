import React from 'react'
export default function TrustMesh(){
  return (<section>
    <h2>TrustMesh Federation</h2>
    <p>TrustScore по арендаторам влияет на комиссии, лимиты и приоритеты.</p>
    <ul>
      <li>POST /api/trustmesh/score/report</li>
      <li>GET  /api/trustmesh/aggregate?tenant=...</li>
      <li>POST /api/trustmesh/bridge/verify</li>
    </ul>
  </section>)
}
