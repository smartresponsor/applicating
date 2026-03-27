import axios from 'axios'
import { getToken } from './auth'
const api = axios.create({ baseURL: import.meta.env.VITE_API_URL || '/api/admin' })
api.interceptors.request.use(cfg=>{ const t=getToken(); if(t) cfg.headers['Authorization']='Bearer '+t; return cfg })
export const fetchTenants = () => api.get('/tenants').then(r=>r.data)
export const fetchInvoices = () => api.get('/billing/invoices').then(r=>r.data)
export const fetchUsage = (tenant?: string) => api.get('/usage', { params: { tenant }}).then(r=>r.data)
export const fetchIncidents = () => api.get('/incidents').then(r=>r.data)
export const fetchStripe = (tenantId: string) => api.get(`/billing/stripe/${tenantId}`).then(r=>r.data)
export default api
