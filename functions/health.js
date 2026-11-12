import {json, gasGet, errorResponse} from './_gas.js';
export async function onRequest() {
  try { const r = await gasGet({action:'health'}); return json({result:'success',backend:'Google Apps Script + Google Sheets',googleSheets:r.data}); }
  catch (error) { return errorResponse(error); }
}
