import React from 'react'; import { NavLink } from 'react-router-dom'
const s=(a:boolean)=>({display:'block',padding:'10px',textDecoration:'none',background:a?'#eee':''})
export default ()=> (<aside style={{width:220,padding:12,borderRight:'1px solid #ddd'}}>
<h3>Admin</h3>
<nav>
<NavLink to='/tenants' style={({isActive})=>s(isActive)}>Tenants</NavLink>
<NavLink to='/billing' style={({isActive})=>s(isActive)}>Billing</NavLink>
<NavLink to='/usage' style={({isActive})=>s(isActive)}>Usage</NavLink>
<NavLink to='/incidents' style={({isActive})=>s(isActive)}>Incidents</NavLink>
</nav></aside>)