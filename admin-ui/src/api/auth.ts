import axios from 'axios'
const auth = axios.create({ baseURL: import.meta.env.VITE_API_URL || '/api/admin' })
export async function login(email:string, password:string){
  const {data} = await auth.post('/auth/login',{email,password})
  localStorage.setItem('admin_jwt', data.token)
  return data
}
export function getToken(){ return localStorage.getItem('admin_jwt') || '' }
export function logout(){ localStorage.removeItem('admin_jwt') }
export default auth
