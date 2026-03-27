import React from 'react'
export default function LegalAssurance(){
  return (<section>
    <h2>Legal & Assurance Mesh</h2>
    <ul>
      <li>POST /api/legal/template {name, context(json)}</li>
      <li>POST /api/legal/dpa</li>
      <li>POST /api/legal/assurance/check {rules(json)}</li>
    </ul>
  </section>)
}
