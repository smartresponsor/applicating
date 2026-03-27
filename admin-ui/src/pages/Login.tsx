import React,{useState} from 'react'
import { useNavigate } from 'react-router-dom'
import { login } from '../api/auth'
export default function Login(){
  const nav = useNavigate()
  const [email,setEmail]=useState('admin@example.com')
  const [password,setPassword]=useState('admin')
  const [err,setErr]=useState('')
  const onSubmit=async(e:any)=>{e.preventDefault(); try{ await login(email,password); nav('/tenants') }catch(ex:any){ setErr('Login failed') } }
  return (<div style={{display:'grid',placeItems:'center',height:'100vh',fontFamily:'Inter,system-ui'}}>
    <form onSubmit={onSubmit} style={{border:'1px solid #ddd',padding:24,borderRadius:8,width:320}}>
      <h3>Admin Login</h3>
      <label>Email</label><input value={email} onChange={e=>setEmail(e.target.value)} style={{width:'100%'}}/>
      <label>Password</label><input type="password" value={password} onChange={e=>setPassword(e.target.value)} style={{width:'100%'}}/>
      {err && <p style={{color:'crimson'}}>{err}</p>}
      <button type="submit" style={{marginTop:12,width:'100%'}}>Sign in</button>
    </form></div>)
}
