import React from 'react'
export default function FederationLearning(){
  return (<section>
    <h2>Self-Learning Federation</h2>
    <ul>
      <li>GET  /api/policy/federation/learning/export?policy=...</li>
      <li>POST /api/policy/federation/learning/apply {payload, signature}</li>
    </ul>
  </section>)
}
