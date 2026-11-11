import {db,json} from './_db.js';

export const handler = async () => {
  try {
    const result = await db().query('SELECT 1 AS ok, NOW() AS server_time');
    return json(200, {result:'success', database:'connected', server_time:result.rows[0].server_time});
  } catch (error) {
    console.error('Health check failed:', error);
    return json(503, {result:'error', database:'unavailable'});
  }
};
