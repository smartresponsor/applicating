import React from 'react'
export default function PolicyEngine(){
  return (<section>
    <h2>Policy-as-Code Engine</h2>
    <p>Единый реестр политик, декларативная проверка (when/allow/effect/deny) и Zero‑Trust.</p>
    <ul>
      <li>POST /api/policy/eval</li>
      <li>POST /api/policy/reload</li>
    </ul>
  </section>)
}
