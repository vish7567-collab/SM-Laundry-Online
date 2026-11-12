import { handler } from '../netlify/functions/health.js';
import { toResponse } from './_adapter.js';
export async function onRequest(context) {
  try { return toResponse(await handler({}, context.env)); }
  catch (error) { console.error('Health function error:', error); return Response.json({result:'error',database:'unavailable'},{status:503,headers:{'Cache-Control':'no-store'}}); }
}
