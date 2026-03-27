import React from 'react'
export default function PriorityTraffic(){
  return (<section>
    <h2>Priority Queues & Traffic Shaping</h2>
    <p>Очереди с приоритетами (VIP/Pro/Standard) и лимиты с учётом TrustScore.</p>
    <ul>
      <li>POST /api/traffic/enqueue</li>
      <li>POST /api/traffic/next</li>
    </ul>
  </section>)
}
