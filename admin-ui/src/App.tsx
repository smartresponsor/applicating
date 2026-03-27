import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import Tenants from './pages/Tenants'
import Billing from './pages/Billing'
import Usage from './pages/Usage'
import Incidents from './pages/Incidents'
import Login from './pages/Login'
import Nav from './components/Nav'
import RequireRole from './components/RequireRole'
export default function App(){
  return (<div style={{display:'flex',minHeight:'100vh',fontFamily:'Inter,system-ui'}}>
    <Nav/>
    <main style={{flex:1,padding:'24px'}}>
      <Routes>
        <Route path="/login" element={<Login/>}/>
        <Route path="/" element={<Navigate to="/tenants" />} />
        <Route path="/tenants" element={<RequireRole><Tenants/></RequireRole>} />
        <Route path="/billing" element={<RequireRole><Billing/></RequireRole>} />
        <Route path="/usage" element={<RequireRole><Usage/></RequireRole>} />
        <Route path="/incidents" element={<RequireRole><Incidents/></RequireRole>} />
      </Routes>
    </main>
  </div>)
}
