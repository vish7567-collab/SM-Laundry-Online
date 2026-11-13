import {readCookie, sessionCookie, json, gasGet, gasPost, errorResponse} from './_gas.js';

export async function onRequest(context) {
  const {request} = context;
  try {
    const token = readCookie(request);
    if (!token) return json({result:'error',message:'Session expired. Please login again.'},401);
    if (request.method === 'GET') {
      const params = Object.fromEntries(new URL(request.url).searchParams.entries());
      const result = await gasGet({...params, token});
      const expired = result.data?.result === 'error' && /Session expired/i.test(result.data.message||'');
      return json(result.data, expired ? 401 : 200, expired ? {'Set-Cookie':sessionCookie('',0)} : {'Set-Cookie':sessionCookie(token)});
    }
    if (request.method === 'POST') {
      const body = await request.json().catch(()=>({}));
      const result = await gasPost({...body, _token:token});
      const expired = result.data?.result === 'error' && /Session expired/i.test(result.data.message||'');
      return json(result.data, expired ? 401 : 200, expired ? {'Set-Cookie':sessionCookie('',0)} : {'Set-Cookie':sessionCookie(token)});
    }
    return json({result:'error',message:'Method not allowed'},405);
  } catch (error) { return errorResponse(error); }
}
