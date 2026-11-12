import { handler } from '../netlify/functions/auth.js';
import { makeEvent, toResponse } from './_adapter.js';
export async function onRequest(context) {
  try { return toResponse(await handler(await makeEvent(context.request), context.env)); }
  catch (error) { console.error('Auth function error:', error); return Response.json({result:'error',message:'Authentication service unavailable.'},{status:500,headers:{'Cache-Control':'no-store'}}); }
}
