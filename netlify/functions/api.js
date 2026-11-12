import jwt from 'jsonwebtoken';
import {db,json,bodyOf} from './_db.js';
import {verify} from './auth.js';
const cookie=t=>`sm_session=${t}; Path=/; HttpOnly; Secure; SameSite=Lax; Max-Age=600`;
const ok=(data={})=>({result:'success',...data});
const fail=(message,status=400)=>json(status,{result:'error',message});
const digits=s=>String(s||'').replace(/[^0-9]/g,'');
const validDate=s=>/^\d{4}-\d{2}-\d{2}$/.test(String(s||''));
export const handler=async (event, env={})=>{
 const session=verify(event, env); if(!session) return fail('Session expired. Please login again.',401);
 try {
  const pool=db(env), client=await pool.connect();
  try {
   let result, payload;
   if(event.httpMethod==='GET'){
    const a=event.queryStringParameters||{}, action=a.action||'';
    if(action==='listInstitutions'){
      result=await client.query("SELECT id,institution_name,mobile_number,status,created_at,updated_at FROM institutions WHERE status='Active' ORDER BY institution_name");
      payload=ok({records:result.rows});
    } else if(action==='listRates'){
      result=a.service_type?await client.query('SELECT id,service_type,item_name,rate FROM rates WHERE service_type=$1 ORDER BY id',[a.service_type]):await client.query('SELECT id,service_type,item_name,rate FROM rates ORDER BY service_type,id');
      payload=ok({records:result.rows});
    } else if(action==='getInstitutionMobile'){
      result=await client.query('SELECT mobile_number FROM institutions WHERE institution_name=$1 AND status=$2 LIMIT 1',[a.name||'', 'Active']); payload=ok({mobile_number:result.rows[0]?.mobile_number||''});
    } else if(action==='dcbReport'){
      const phone=digits(a.phone); if(!phone) return fail('Valid phone number required');
      result=await client.query("SELECT name,phone FROM orders WHERE regexp_replace(coalesce(phone,''),'[^0-9]','','g')=$1 ORDER BY created_at DESC LIMIT 1",[phone]);
      const customer=result.rows[0]; if(!customer) return fail('Customer not found',404);
      const deb=await client.query(`SELECT id AS ref,COALESCE(NULLIF("isoDate",''),to_char(to_date("date",'DD/MM/YYYY'),'YYYY-MM-DD')) AS txn_date,'Debit' AS txn_type,COALESCE(amount,0)+COALESCE("extraCharge",0) AS debit,0 AS credit,'Order '||id AS particulars,created_at FROM orders WHERE regexp_replace(coalesce(phone,''),'[^0-9]','','g')=$1`,[phone]);
      const cred=await client.query("SELECT id::text AS ref,received_date::text AS txn_date,'Credit' AS txn_type,0 AS debit,received_amount AS credit,'Payment - '||instrument_type AS particulars,created_at FROM collection WHERE regexp_replace(coalesce(phone,''),'[^0-9]','','g')=$1",[phone]);
      const tx=[...deb.rows,...cred.rows].sort((x,y)=>String(x.txn_date).localeCompare(String(y.txn_date))||String(x.created_at||'').localeCompare(String(y.created_at||'')));
      const total_debit=tx.reduce((s,x)=>s+Number(x.debit||0),0),total_credit=tx.reduce((s,x)=>s+Number(x.credit||0),0);
      const opening_balance=0;
      const closing_balance=opening_balance+total_debit-total_credit;
      payload=ok({customer,transactions:tx,total_debit,total_credit,opening_balance,closing_balance,balance:closing_balance});
    } else if(action==='institutionDailyItemReport'){
      const {institution,start,end}=a; if(!institution||!validDate(start)||!validDate(end)||start>end) return fail('Invalid institution or date range');
      result=await client.query(`SELECT "date","isoDate",name,items,details FROM orders WHERE name=$1 AND COALESCE(NULLIF("isoDate",''),to_char(to_date("date",'DD/MM/YYYY'),'YYYY-MM-DD')) BETWEEN $2 AND $3 ORDER BY 2`,[institution,start,end]);
      const dates=[]; for(let d=new Date(start+'T00:00:00Z'),last=new Date(end+'T00:00:00Z');d<=last;d.setUTCDate(d.getUTCDate()+1)) dates.push(d.toISOString().slice(0,10));
      const map={}; for(const o of result.rows){let items=Array.isArray(o.items)?o.items:(typeof o.items==='string'?JSON.parse(o.items||'[]'):[]); for(const it of items||[]){const name=String(it.name||'').trim(),qty=Number(it.qty||0),amount=Number(it.amount??qty*Number(it.rate||0));if(!name||qty<=0)continue;const day=o.isoDate||start;map[name]??={item:name,days:{},total:0,weightedRateAmount:0,amount:0};map[name].days[day]=(map[name].days[day]||0)+qty;map[name].total+=qty;map[name].weightedRateAmount+=amount;map[name].amount+=amount;}}
      const rows=Object.values(map).map(x=>({...x,rate:x.total?x.weightedRateAmount/x.total:0})).map(({weightedRateAmount,...x})=>x).sort((x,y)=>x.item.localeCompare(y.item));
      payload=ok({institution,start,end,dates,rows,totalQty:rows.reduce((s,x)=>s+x.total,0),grandTotal:rows.reduce((s,x)=>s+x.amount,0)});
    } else {
      result=await client.query('SELECT * FROM orders ORDER BY created_at DESC');
      const orders=result.rows.map(o=>{let items=o.items;if(typeof items==='string'){try{items=JSON.parse(items)}catch{items=[]}}if(!Array.isArray(items))items=[];return {...o,items,collection_entries:[],received_total:0,received_amount:0,received_date:null,instrument_type:null}});
      const payments=await client.query('SELECT id,order_id,phone,received_amount,received_date,instrument_type,created_at FROM collection ORDER BY received_date DESC,id DESC');
      const by={};for(const p of payments.rows){const k=String(p.order_id??'');(by[k]??=[]).push(p)}
      for(const o of orders){
        const es=by[String(o.id)]||[];
        o.collection_entries=es;
        o.received_total=es.reduce((s,x)=>s+Number(x.received_amount),0);
        o.received_amount=o.received_total;
        o.received_date=es[0]?.received_date||null;
        o.instrument_type=es[0]?.instrument_type||null;
        const payable=Number(o.amount||0)+Number(o.extraCharge||0);
        o.payable_amount=payable;
        o.payment_status=o.received_total>=payable-0.009?'Payment Completed':(o.received_total>0?'Part Payment':'');
      }
      payload=orders;
    }
   } else if(event.httpMethod==='POST'){
    const d=bodyOf(event),a=d.action||'';
    if(a==='saveRate'){
      result=await client.query('INSERT INTO rates(service_type,item_name,rate) VALUES($1,$2,$3) ON CONFLICT(service_type,item_name) DO UPDATE SET rate=EXCLUDED.rate,updated_at=NOW()',[d.service_type,d.item_name,Number(d.rate||0)]);payload=ok({message:'Rate saved'});
    } else if(a==='saveRates'){
      let saved=0;await client.query('BEGIN');try{for(const r of d.rates||[]){if(!r.service_type||!r.item_name)continue;await client.query('INSERT INTO rates(service_type,item_name,rate) VALUES($1,$2,$3) ON CONFLICT(service_type,item_name) DO UPDATE SET rate=EXCLUDED.rate,updated_at=NOW()',[r.service_type,r.item_name,Number(r.rate||0)]);saved++;}await client.query('COMMIT')}catch(e){await client.query('ROLLBACK');throw e}payload=ok({message:'Rates saved',saved});
    } else if(a==='addInstitution'||a==='updateInstitution'){
      if(!d.institution_name||!d.mobile_number)return fail('Institution name and mobile number are required');
      if(a==='updateInstitution'&&d.id) await client.query('UPDATE institutions SET institution_name=$1,mobile_number=$2,status=$3,updated_at=NOW() WHERE id=$4',[d.institution_name,d.mobile_number,d.status||'Active',d.id]);
      else await client.query('INSERT INTO institutions(institution_name,mobile_number,status) VALUES($1,$2,$3)',[d.institution_name,d.mobile_number,d.status||'Active']);payload=ok({message:'Institution saved'});
    } else if(a==='deleteInstitution'){
      await client.query("UPDATE institutions SET status='Inactive',updated_at=NOW() WHERE id=$1",[d.id]);payload=ok({message:'Institution deleted'});
    } else if(a==='addBulkCollection'||a==='addReconciliation'||a==='updateReconciliation'){
      const amount=Number(d.received_amount),date=d.received_date,mode=d.instrument_type;if(!amount||amount<=0||!validDate(date)||!['Cash','UPI'].includes(mode))return fail('Invalid collection details');
      let phone=String(d.phone||'');let orderId=d.id||null;
      if(a==='addBulkCollection'){const c=await client.query("SELECT phone FROM orders WHERE regexp_replace(coalesce(phone,''),'[^0-9]','','g')=$1 LIMIT 1",[digits(phone)]);if(!c.rowCount)return fail('ഈ ഫോൺ നമ്പറിൽ കസ്റ്റമറെ കണ്ടെത്തിയില്ല');}
      else {const c=await client.query("SELECT phone FROM orders WHERE id=$1 AND status IN ('Completed','Delivered','Part Bill','Part Payment') AND COALESCE(collection,0)>0",[d.id]);if(!c.rowCount)return fail('Delivered order not found');phone=phone||c.rows[0].phone;}
      const ins=await client.query('INSERT INTO collection(order_id,phone,received_amount,received_date,instrument_type) VALUES($1,$2,$3,$4,$5) RETURNING id',[a==='addBulkCollection'?null:orderId,phone,amount,date,mode]);
      let paymentStatus=null,receivedTotal=null;
      if(a!=='addBulkCollection' && orderId){
        const sums=await client.query(`SELECT COALESCE(SUM(received_amount),0) AS paid, COALESCE(o.amount,0)+COALESCE(o.\"extraCharge\",0) AS payable FROM orders o LEFT JOIN collection c ON c.order_id=o.id WHERE o.id=$1 GROUP BY o.id,o.amount,o.\"extraCharge\"`,[orderId]);
        if(sums.rowCount){receivedTotal=Number(sums.rows[0].paid);const payable=Number(sums.rows[0].payable);paymentStatus=receivedTotal>=payable-0.009?'Payment Completed':'Part Payment';await client.query('UPDATE orders SET status=$1 WHERE id=$2',[paymentStatus,orderId]);}
      }
      payload=ok({message:'Collection saved',collection_id:ins.rows[0].id,payment_status:paymentStatus,received_total:receivedTotal});
    } else if(a==='deleteOrder'){
      await client.query('DELETE FROM orders WHERE id=$1',[d.id]);payload=ok({message:'Order Deleted'});
    } else if(a==='updateStatus'){
      if(!['Pending','Completed'].includes(d.status))return fail('Invalid status update');
      const u=await client.query("UPDATE orders SET status=$1 WHERE id=$2 AND status NOT IN ('Delivered','Payment Completed','Part Payment') RETURNING id",[d.status,d.id]);
      if(!u.rowCount)return fail('Payment Completed / Part Payment / Delivered ഓർഡറുകളുടെ സ്റ്റാറ്റസ് മാറ്റാൻ കഴിയില്ല');
      payload=ok({message:'Status Updated'});
    } else if(a==='updateDelivery'){
      const u=await client.query(d.delivered?`UPDATE orders SET collection=COALESCE(amount,0)+COALESCE("extraCharge",0),status='Delivered' WHERE id=$1 AND status NOT IN ('Payment Completed','Part Payment')`:`UPDATE orders SET collection=0 WHERE id=$1`,[d.id]);
      if(d.delivered && !u.rowCount)return fail('Payment Completed / Part Payment ഓർഡറുകളുടെ സ്റ്റാറ്റസ് മാറ്റാൻ കഴിയില്ല');
      payload=ok({message:'Delivery saved',id:d.id,delivered:!!d.delivered});
    } else if(a==='updateDeliveryCollection'){
      let updated=0;await client.query('BEGIN');try{for(const x of d.updates||[]){const u=await client.query(`UPDATE orders SET collection=CASE WHEN $1 THEN ROUND(COALESCE(amount,0)+COALESCE("extraCharge",0),2) ELSE 0 END,status=CASE WHEN $1 THEN 'Delivered' ELSE status END WHERE id=$2 AND status IN ('Completed','Delivered') AND status NOT IN ('Payment Completed','Part Payment')`,[!!x.delivered,x.id]);updated+=u.rowCount;}await client.query('COMMIT')}catch(e){await client.query('ROLLBACK');throw e}payload=ok({message:'Delivery വിവരങ്ങൾ വിജയകരമായി സേവ് ചെയ്തു',updated});
    } else if(a==='updateCollection'){
      await client.query('UPDATE orders SET collection=$1 WHERE id=$2',[Number(d.collection||0),d.id]);payload=ok({message:'Collection updated'});
    } else {
      // Save or edit order (the frontend submits the complete order object).
      const vals=[String(d.id||''),d.date||null,d.isoDate||null,d.time||null,d.name||null,d.phone||null,Number(d.count||0),d.service||null,JSON.stringify(d.items||[]),Number(d.extraCharge||0),String(d.details??d.detail??''),Number(d.amount||0),d.status||'Pending'];
      if(!vals[0])return fail('Order ID is required');
      await client.query(`INSERT INTO orders(id,"date","isoDate","time",name,phone,"count",service,items,"extraCharge",details,amount,status) VALUES($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13) ON CONFLICT(id) DO UPDATE SET "date"=EXCLUDED."date","isoDate"=EXCLUDED."isoDate","time"=EXCLUDED."time",name=EXCLUDED.name,phone=EXCLUDED.phone,"count"=EXCLUDED."count",service=EXCLUDED.service,items=EXCLUDED.items,"extraCharge"=EXCLUDED."extraCharge",details=EXCLUDED.details,amount=EXCLUDED.amount,status=CASE WHEN orders.status IN ('Payment Completed','Part Payment') THEN orders.status ELSE EXCLUDED.status END`,vals);payload=ok();
    }
   } else return fail('Method not allowed',405);
   const renewed=jwt.sign({sub:session.sub},env.JWT_SECRET,{expiresIn:'10m'});
   return json(200,payload,{'Set-Cookie':cookie(renewed)});
  } finally {client.release(); await pool.end().catch(()=>{});}
 } catch(e){console.error(e);return json(500,{result:'error',message:'Server error. Check Cloudflare Pages function logs.'});}
};
