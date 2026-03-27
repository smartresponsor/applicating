import React from 'react'
export default function SentientLoop(){
  return (<section>
    <h2>Sentient Intelligence Loop</h2>
    <ul>
      <li>POST /api/intelligence/feedback {policy, region, sla_ok, latency_ms, risk, trust}</li>
      <li>GET  /api/intelligence/predict?window=60</li>
      <li>POST /api/intelligence/train {window}</li>
      <li>POST /api/intelligence/adjust {thresholds(json)}</li>
    </ul>
  </section>)
}
