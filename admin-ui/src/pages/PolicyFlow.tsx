import React from 'react'
export default function PolicyFlow(){
  return (<section>
    <h2>Policy Flow Engine</h2>
    <p>Оркестрация: evaluate → connectors (trust/rate/billing) → лог.</p>
    <ul>
      <li>POST /api/policy/flow/run</li>
      <li>GET  /api/policy/flow/status</li>
    </ul>
  </section>)
}
