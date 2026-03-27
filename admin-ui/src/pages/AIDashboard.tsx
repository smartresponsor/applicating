import React, { useEffect, useState } from 'react'
type Row = { ts?: string; tenant?: string; qps?: number; error?: number; p95?: number; decision?: string }
export default function AIDashboard(){
  const [rows, setRows] = useState<Row[]>([])
  useEffect(()=>{
    // demo: статические данные как заглушка
    setRows([
      {tenant:'tenantA', qps: 120, error: 0.01, p95: 180, decision: 'replicas +1'},
      {tenant:'tenantB', qps: 40,  error: 0.00, p95: 95,  decision: 'stable'}
    ])
  },[])
  return (<section>
    <h2>AI Dashboard — Live</h2>
    <p>Поток телеметрии и решения AI (autoscale / policy).</p>
    <table>
      <thead><tr><th>Tenant</th><th>QPS</th><th>Error</th><th>P95 (ms)</th><th>AI Decision</th></tr></thead>
      <tbody>
        {rows.map((r,i)=>(<tr key={i}>
          <td>{r.tenant}</td><td>{r.qps}</td><td>{r.error}</td><td>{r.p95}</td><td>{r.decision}</td>
        </tr>))}
      </tbody>
    </table>
  </section>)
}
