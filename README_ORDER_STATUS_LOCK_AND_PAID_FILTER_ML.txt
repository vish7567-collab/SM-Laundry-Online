SM LAUNDRY – ORDER STATUS LOCK & PAID FILTER UPDATE

മാറ്റങ്ങൾ:
1. Delivered, Part Bill, Part Payment, Payment Completed എന്നീ status-ുകൾ UI-ൽ locked ആണ്. Status badge ക്ലിക്ക് ചെയ്താലും മാറ്റാൻ കഴിയില്ല.
2. Backend updateStatus action-ലും ഇതേ lock നിർബന്ധമാക്കി. API നേരിട്ട് വിളിച്ചാലും ഈ status-ുകൾ മാറ്റാൻ കഴിയില്ല.
3. Existing order edit/save ചെയ്യുമ്പോൾ പഴയ status നിലനിർത്തും. Order edit request വഴി status മാറ്റുന്നത് തടഞ്ഞു. പുതിയ order-ന് മാത്രം default Pending / നൽകിയ status ഉപയോഗിക്കും.
4. Orders screen-ൽ Paid / Part Payment toggle OFF ആയിരിക്കുമ്പോൾ Payment Completed, Part Payment, Part Bill status-ിലുള്ള ഓർഡറുകൾ മറയ്ക്കും. Toggle ON ആക്കുമ്പോൾ അവ സാധാരണ ഓർഡറുകൾക്കൊപ്പം കാണിക്കും.
5. Delivered status lock ആണ്; പക്ഷേ Paid/Part Payment toggle-ന്റെ ഭാഗമായി Delivered ഓർഡറുകൾ മറയ്ക്കുന്നില്ല.

DEPLOY:
- ZIP extract ചെയ്യുക.
- public/index.html, google-apps-script/Code.gs എന്നിവ GitHub repository-യിലെ അതേ പാതകളിൽ replace ചെയ്ത് commit/push ചെയ്യുക.
- Cloudflare Pages Auto Deploy പൂർത്തിയാകുന്നത് കാത്തിരിക്കുക.
- Google Apps Script-ൽ Code.gs update ചെയ്ത് Save → Deploy → Manage deployments → Edit → New version → Deploy ചെയ്യുക.
- നിലവിലെ Google Apps Script Web App URL മാറ്റരുത്.

ശ്രദ്ധിക്കുക: നിലവിലെ Code.gs-ൽ ഈ പ്രോജക്റ്റിനായി പിന്നീട് ചേർത്ത മറ്റ് മാറ്റങ്ങൾ ഉണ്ടെങ്കിൽ replace ചെയ്യുന്നതിന് മുമ്പ് സംയോജിപ്പിക്കുക. ഈ പാക്കേജ് Google Sheets ഡാറ്റയോ schema-യോ മാറ്റുന്നില്ല.

Validation: HTML inline JavaScript blocks and Code.gs syntax checked with Node.js. Live deployment/runtime test ചെയ്തിട്ടില്ല.
