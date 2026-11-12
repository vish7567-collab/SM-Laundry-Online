import jwt from 'jsonwebtoken';
import bcrypt from 'bcryptjs';
import {json,bodyOf} from './_db.js';
const COOKIE='sm_session';
const cookie=(token,maxAge=600)=>`${COOKIE}=${token}; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age=${maxAge}`;
export function verify(event){
  const raw=event.headers.cookie||event.headers.Cookie||'';
  const token=raw.split(';').map(x=>x.trim()).find(x=>x.startsWith(COOKIE+'='))?.slice(COOKIE.length+1);
  if(!token||!process.env.JWT_SECRET) return null;
  try{return jwt.verify(token,process.env.JWT_SECRET)}catch{return null;}
}
export const handler=async event=>{
 if(event.httpMethod==='GET'){const s=verify(event);if(!s)return json(401,{result:'error',message:'Session expired'});const token=jwt.sign({sub:s.sub},process.env.JWT_SECRET,{expiresIn:'10m'});return json(200,{result:'success',username:s.sub},{'Set-Cookie':cookie(token)});}
 if(event.httpMethod!=='POST') return json(405,{result:'error',message:'Method not allowed'});
 const b=bodyOf(event), action=b.action;
 if(action==='logout') return json(200,{result:'success'},{'Set-Cookie':cookie('',0)});
 if(action!=='login') return json(400,{result:'error',message:'Unknown action'});
 const user=process.env.ADMIN_USERNAME, hash=process.env.ADMIN_PASSWORD_HASH;
 if(!user||!hash||!process.env.JWT_SECRET) return json(503,{result:'error',message:'Admin credentials are not configured in Netlify environment variables.'});
 const ok=String(b.username||'')===user && await bcrypt.compare(String(b.password||''),hash);
 if(!ok) return json(401,{result:'error',message:'Username അല്ലെങ്കിൽ Password തെറ്റാണ്.'});
 const token=jwt.sign({sub:user},process.env.JWT_SECRET,{expiresIn:'10m'});
 return json(200,{result:'success'},{'Set-Cookie':cookie(token)});
};
