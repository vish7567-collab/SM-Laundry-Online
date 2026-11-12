import {readCookie, sessionCookie, json, gasGet, gasPost, errorResponse} from './_gas.js';

export async function onRequest(context) {
  const {request} = context;
  try {
    if (request.method === 'GET') {
      const token = readCookie(request);
      if (!token) return json({result:'error',message:'Session expired'},401);
      // A lightweight authenticated request also refreshes the GAS 10-minute inactivity timer.
      const check = await gasGet({action:'listRates', token});
      if (check.data?.result === 'error') return json({result:'error',message:check.data.message || 'Session expired'},401,{'Set-Cookie':sessionCookie('',0)});
      return json({result:'success'},200,{'Set-Cookie':sessionCookie(token)});
    }
    if (request.method !== 'POST') return json({result:'error',message:'Method not allowed'},405);
    const body = await request.json().catch(()=>({}));
    if (body.action === 'logout') return json({result:'success'},200,{'Set-Cookie':sessionCookie('',0)});
    if (body.action !== 'login') return json({result:'error',message:'Unknown action'},400);
    const result = await gasPost({action:'login', username:body.username, password:body.password});
    if (result.data?.result !== 'success' || !result.data?.token) return json(result.data || {result:'error',message:'Login failed'}, result.data?.result==='error'?401:502);
    return json({result:'success',username:result.data.username || body.username},200,{'Set-Cookie':sessionCookie(result.data.token)});
  } catch (error) { return errorResponse(error); }
}
