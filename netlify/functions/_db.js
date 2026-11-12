import { Pool, neonConfig } from '@neondatabase/serverless';

// Cloudflare Workers provides a native WebSocket implementation.
neonConfig.webSocketConstructor = WebSocket;
export function db(env) {
  if (!env?.DATABASE_URL) throw new Error('DATABASE_URL environment variable is missing');
  // Cloudflare isolates must not reuse WebSocket pools across requests.
  return new Pool({ connectionString: env.DATABASE_URL, max: 3, connectionTimeoutMillis: 10000 });
}
export const json = (statusCode, body, headers = {}) => ({
  statusCode,
  headers: { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store', ...headers },
  body: JSON.stringify(body)
});
export function bodyOf(event) { try { return JSON.parse(event.body || '{}'); } catch { return {}; } }
