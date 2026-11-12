const COOKIE = 'sm_gas_session';
const GAS_URL = 'https://script.google.com/macros/s/AKfycbw27bi-17ub4mdWS-fasQ7zF2SGUfpR-XlL9wi_wzGJlUnWqxEsSkVepk8fMG4We4jD/exec';

export function readCookie(request) {
  const raw = request.headers.get('Cookie') || '';
  const item = raw.split(';').map(x => x.trim()).find(x => x.startsWith(COOKIE + '='));
  return item ? decodeURIComponent(item.slice(COOKIE.length + 1)) : '';
}
export function sessionCookie(token, age=600) {
  return `${COOKIE}=${encodeURIComponent(token)}; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age=${age}`;
}
export function json(data, status=200, headers={}) {
  return Response.json(data, {status, headers:{'Cache-Control':'no-store', ...headers}});
}
export async function gasGet(params) {
  const url = new URL(GAS_URL);
  for (const [k,v] of Object.entries(params)) if (v !== undefined && v !== null) url.searchParams.set(k, String(v));
  const response = await fetch(url.toString(), {redirect:'follow', headers:{'Accept':'application/json'}});
  const text = await response.text();
  try { return {httpStatus:response.status, data:JSON.parse(text)}; }
  catch { throw new Error(`Google Apps Script returned non-JSON response (HTTP ${response.status}). Check Web App deployment access.`); }
}
export async function gasPost(payload) {
  const response = await fetch(GAS_URL, {method:'POST', redirect:'follow', headers:{'Content-Type':'text/plain;charset=utf-8','Accept':'application/json'}, body:JSON.stringify(payload)});
  const text = await response.text();
  try { return {httpStatus:response.status, data:JSON.parse(text)}; }
  catch { throw new Error(`Google Apps Script returned non-JSON response (HTTP ${response.status}). Check Web App deployment access.`); }
}
export function errorResponse(error) {
  console.error('Google Sheets API proxy:', error);
  return json({result:'error', message:error?.message || 'Google Sheets service unavailable.'}, 502);
}
