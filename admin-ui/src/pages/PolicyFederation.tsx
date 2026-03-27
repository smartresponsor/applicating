import React from 'react'
export default function PolicyFederation(){
  return (<section>
    <h2>Policy Federation (Multi-Region)</h2>
    <ul>
      <li>GET/POST /api/policy/federation/peers</li>
      <li>POST /api/policy/federation/replicate</li>
      <li>POST /api/policy/federation/push</li>
    </ul>
  </section>)
}
