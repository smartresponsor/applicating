import React from 'react'
import { Navigate } from 'react-router-dom'
import { getToken } from '../api/auth'
export default function RequireRole({children}:{children:any}){
  const t = getToken()
  if(!t) return <Navigate to='/login' replace />
  return children
}
