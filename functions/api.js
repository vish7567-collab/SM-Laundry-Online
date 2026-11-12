import { handler } from '../netlify/functions/api.js';
import { makeEvent, toResponse } from './_adapter.js';
export async function onRequest(context) {
  try { return toResponse(await handler(await makeEvent(context.request), context.env)); }
  catch (error) { console.error('API function error:', error); return Response.json({result:'error',message:'API service unavailable.'},{status:500,headers:{'Cache-Control':'no-store'}}); }
}
