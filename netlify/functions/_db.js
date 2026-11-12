import pg from 'pg';
const { Pool } = pg;
let pool;
export function db(){
  if(!process.env.DATABASE_URL) throw new Error('DATABASE_URL environment variable is missing');
  if(!pool) pool=new Pool({connectionString:process.env.DATABASE_URL,ssl:{rejectUnauthorized:false},max:3,idleTimeoutMillis:10000,connectionTimeoutMillis:10000});
  return pool;
}
export const json=(statusCode,body,headers={})=>({statusCode,headers:{'Content-Type':'application/json; charset=utf-8','Cache-Control':'no-store',...headers},body:JSON.stringify(body)});
export function bodyOf(event){try{return JSON.parse(event.body||'{}')}catch{return {}}}
