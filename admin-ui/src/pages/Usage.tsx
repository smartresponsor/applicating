import React,{useEffect,useState} from 'react'
import { fetchUsage } from '../api'
export default function Usage(){
  const [rows,setRows]=useState<any[]>([])
  useEffect(()=>{ fetchUsage().then(setRows)},[])
  return (<section>
    <h2>Usage (current month)</h2>
    <table style={{width:'100%',borderCollapse:'collapse'}}>
      <thead><tr><th>Tenant</th><th>Requests</th><th>Quota</th><th>Within</th></tr></thead>
      <tbody>{rows.map((r:any)=>(<tr key={r.tenant_id}><td>{r.tenant_id}</td><td>{r.requests}</td><td>{r.quota}</td><td>{r.within?'yes':'no'}</td></tr>))}</tbody>
    </table>
  </section>)
}
