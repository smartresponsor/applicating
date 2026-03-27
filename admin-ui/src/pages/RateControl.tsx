import React from 'react'
export default function RateControl(){
  return (<section>
    <h2>Global Rate Control & Quarantine</h2>
    <ul>
      <li>GET /api/rate/mode — текущий режим</li>
      <li>POST /api/rate/quarantine/put — карантин IP/tenant</li>
      <li>POST /api/rate/envoy/export — экспорт лимитов в Envoy</li>
    </ul>
  </section>)
}
