import React from 'react'

export default function Autopilot(){
  return (
    <section>
      <h2>Autopilot Status</h2>
      <p>Nightly pipeline: forecast → policy → action → audit.</p>
      <ul>
        <li>🟢 stable — no action</li>
        <li>🟡 adjusting — scaling</li>
        <li>🔴 rollback — intervention</li>
      </ul>
      <p>Backend должен отдавать последние записи из <code>autopilot_events</code>.</p>
    </section>
  )
}
