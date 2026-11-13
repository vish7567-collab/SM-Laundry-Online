SM LAUNDRY – SPEED OPTIMIZATION (CLOUDFLARE PAGES + GOOGLE SHEETS)

പ്രധാന മാറ്റങ്ങൾ:
1. Dashboard startup-ൽ Orders, Rates parallel ആയി ലോഡ് ചെയ്യും.
2. എല്ലാ service rates-ഉം ഒരൊറ്റ listRates API request-ൽ ലഭിക്കും (മുമ്പ് മൂന്ന് sequential requests).
3. ഒരേസമയം വരുന്ന full-rate load calls ഒരേ in-flight request share ചെയ്യും.
4. Login/session validation ഇനി Rates sheet വായിക്കാതെ lightweight sessionCheck ഉപയോഗിക്കും.
5. ഓരോ API interaction-ലും 10-minute rolling session cookie refresh ചെയ്യും; GAS cache session-വും request-ൽ refresh ചെയ്യുന്നു.
6. Existing frontend screens, orders, collections, reports, printing എന്നിവ മാറ്റിയിട്ടില്ല.

DEPLOY STEPS
A. ZIP extract ചെയ്യുക. GitHub repository root-ൽ ഉള്ള ഫയലുകൾ replace/commit ചെയ്യുക.
B. Google Apps Script editor തുറന്ന് google-apps-script/Code.gs-ലെ മുഴുവൻ code നിലവിലുള്ള Code.gs-ൽ replace ചെയ്യുക. Spreadsheet-ൽ setupDatabase() വീണ്ടും run ചെയ്യേണ്ടതില്ല.
C. Apps Script > Deploy > Manage deployments > Edit (pencil) > Version: New version > Deploy. നിലവിലുള്ള Web App URL തന്നെ നിലനിർത്തുക. Execute as Me / access Anyone ക്രമീകരണം നിലവിലുള്ളതുപോലെ തന്നെ വേണം.
D. Cloudflare Pages Git deployment പൂർത്തിയാകുന്നത് വരെ കാത്തിരിക്കുക. Build command blank; output directory public; root directory blank.
E. ആദ്യം /health പരിശോധിക്കുക. തുടർന്ന് login, dashboard, rates, orders, collections പരിശോധിക്കുക.

IMPORTANT
- Google Sheets-ൽ നിലവിലുള്ള ഡാറ്റ മാറ്റുകയോ delete ചെയ്യുകയോ ചെയ്യുന്നില്ല.
- setupDatabase() അല്ലെങ്കിൽ setupAdmin() വീണ്ടും run ചെയ്യരുത്.
- Code.gs update + New version deployment നിർബന്ധമാണ്; അല്ലെങ്കിൽ sessionCheck action പഴയ backend-ൽ ലഭിക്കില്ല.
- ഇത് round-trip കുറയ്ക്കുന്ന optimization ആണ്. Google Apps Script cold-start, sheet size, network latency എന്നിവ തുടർന്നും ബാധിക്കാം.
