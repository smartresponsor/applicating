import React from 'react'
export default function AdaptiveLearning(){
  return (<section>
    <h2>Adaptive Policy Learning</h2>
    <ul>
      <li>POST /api/policy/learning/variant/define</li>
      <li>POST /api/policy/learning/assign</li>
      <li>POST /api/policy/learning/feedback</li>
      <li>GET  /api/policy/learning/status?policy=...</li>
    </ul>
  </section>)
}
