import React from 'react'
export default function AbuseGuard(){
  return (<section>
    <h2>Anti‑DDoS & Abuse Guard</h2>
    <ul>
      <li>Middleware: блок по бан‑листу и burst‑эвристике</li>
      <li>API: /api/guard/captcha/verify</li>
      <li>Логи: abuse_events, баны: abuse_bans</li>
    </ul>
  </section>)
}
