import {readCookie, json, gasGet, gasPost, errorResponse} from './_gas.js';

export async function onRequest(context) {
  const {request} = context;
  try {
    const token = readCookie(request);
    if (!token) return json({result:'error',message:'Session expired. Please login again.'},401);
    if (request.method === 'GET') {
      const params = Object.fromEntries(new URL(request.url).searchParams.entries());
      const result = await gasGet({...params, token});
      return json(result.data, result.data?.result === 'error' && /Session expired/i.test(result.data.message||'') ? 401 : 200);
    }
    if (request.method === 'POST') {
      const body = await request.json().catch(()=>({}));
      const result = await gasPost({...body, _token:token});
      return json(result.data, result.data?.result === 'error' && /Session expired/i.test(result.data.message||'') ? 401 : 200);
    }
    return json({result:'error',message:'Method not allowed'},405);
  } catch (error) { return errorResponse(error); }
}
