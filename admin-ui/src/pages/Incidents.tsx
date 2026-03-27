import React,{useEffect,useState} from 'react'
import { fetchIncidents } from '../api'
export default ()=>{const [rows,setRows]=useState<any[]>([]); useEffect(()=>{fetchIncidents().then(setRows)},[]);
return (<section><h2>Incidents</h2><ul>{rows.map((x:any)=>(<li key={x.id}><b>{x.alertname}</b> — {x.summary}</li>))}</ul></section>) }