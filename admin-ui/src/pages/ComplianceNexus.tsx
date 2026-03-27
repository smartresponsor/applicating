import React from 'react'
export default function ComplianceNexus(){
  return (<section>
    <h2>Compliance Nexus</h2>
    <ul>
      <li>GET  /api/compliance/policies</li>
      <li>GET  /api/compliance/map?standard=gdpr</li>
      <li>POST /api/compliance/consent/set {subject, purpose, granted}</li>
      <li>GET  /api/compliance/consent/get?subject=...</li>
      <li>POST /api/compliance/retention/schedule {dataset, days}</li>
      <li>POST /api/compliance/retention/sweep</li>
      <li>POST /api/compliance/dpia/evaluate {process, factors(json)}</li>
    </ul>
  </section>)
}
