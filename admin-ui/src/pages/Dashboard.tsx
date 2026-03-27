import React, { useEffect, useState } from 'react'

type KPI = { key:string; label:string; value:number; unit?:string }
type SeriesPoint = { t:string; v:number }
type Chart = { id:string; title:string; series: SeriesPoint[] }

export default function Dashboard(){
  const [kpi, setKpi] = useState<KPI[]>([])
  const [charts, setCharts] = useState<Chart[]>([])

  useEffect(()=>{
    fetch('/api/dashboard/kpi?tenant=tenantA').then(r=>r.json()).then(d=>setKpi(d.kpi||[]))
    fetch('/api/dashboard/charts?tenant=tenantA').then(r=>r.json()).then(d=>setCharts(d.charts||[]))
  }, [])

  return (
    <section>
      <h2>ABI / CLA Dashboard</h2>
      <div style={{display:'grid',gridTemplateColumns:'repeat(5,1fr)',gap:12}}>
        {kpi.map(m => (
          <div key={m.key} style={{padding:12,border:'1px solid #e5e7eb',borderRadius:8}}>
            <div style={{fontSize:12,color:'#6b7280'}}>{m.label}</div>
            <div style={{fontSize:20,fontWeight:700}}>{m.value}{m.unit ? ' ' + m.unit : ''}</div>
          </div>
        ))}
      </div>
      <div style={{marginTop:20, display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:12}}>
        {charts.map(ch => (
          <div key={ch.id} style={{padding:12,border:'1px solid #e5e7eb',borderRadius:8}}>
            <h3 style={{margin:'0 0 8px'}}>{ch.title}</h3>
            <pre style={{fontSize:12, whiteSpace:'pre-wrap'}}>{JSON.stringify(ch.series.slice(0,5), null, 2)}{ch.series.length>5?'\n…':''}</pre>
          </div>
        ))}
      </div>
    </section>
  )
}
