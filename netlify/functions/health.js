import {db,json} from './_db.js';

export const handler = async (_event, env={}) => {
  try {
    const pool = db(env);
    try {
      const result = await pool.query('SELECT 1 AS ok, NOW() AS server_time');
      return json(200, {result:'success', database:'connected', server_time:result.rows[0].server_time});
    } finally { await pool.end().catch(()=>{}); }
  } catch (error) {
    console.error('Health check failed:', error);
    return json(503, {result:'error', database:'unavailable'});
  }
};
