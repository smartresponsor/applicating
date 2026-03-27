import React,{useEffect,useState} from 'react'
import { fetchTenants } from '../api'
export default ()=>{const [rows,setRows]=useState<any[]>([]); const [q,setQ]=useState(''); useEffect(()=>{fetchTenants().then(setRows)},[]);
const f=rows.filter(x=>(x.id||'').includes(q)||(x.name||'').toLowerCase().includes(q.toLowerCase()));
return (<section><h2>Tenants</h2><input placeholder='Search' value={q} onChange={e=>setQ(e.target.value)}/>
<table><thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Plan</th></tr></thead>
<tbody>{f.map((t:any)=>(<tr key={t.id}><td>{t.id}</td><td>{t.name}</td><td>{t.status}</td><td>{t.plan}</td></tr>))}</tbody></table></section>) }