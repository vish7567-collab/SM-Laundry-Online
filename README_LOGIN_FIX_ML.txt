SM LAUNDRY – LOGIN IMMEDIATE LOGOUT FIX

പ്രശ്നത്തിന്റെ കാരണം കണ്ടെത്തി പരിഹരിച്ചു.

Cloudflare auth.js ലോഗിൻ കഴിഞ്ഞ ഉടൻ sessionCheck എന്ന GET request അയയ്ക്കുന്നു. എന്നാൽ Code.gs-ലെ routeGet() ഈ action തിരിച്ചറിയാത്തതിനാൽ “Unknown GET action” ലഭിക്കുകയും auth.js session cookie മായ്ക്കുകയും ചെയ്തു. അതുകൊണ്ടാണ് login ചെയ്ത ഉടനെ logout സംഭവിച്ചത്.

പരിഹാരം:
1. google-apps-script/Code.gs എന്ന പുതുക്കിയ merged ഫയൽ നിലവിലെ Apps Script Code.gs-ൽ ഉപയോഗിക്കുക. പിന്നീട് ചേർത്തിട്ടുള്ള സ്വന്തം മാറ്റങ്ങൾ ഉണ്ടെങ്കിൽ ആദ്യം സംയോജിപ്പിക്കുക.
2. Save ചെയ്യുക.
3. Deploy → Manage deployments → Edit → New version → Deploy.
4. നിലവിലെ Web App URL മാറ്റരുത്.
5. Cloudflare-ൽ GitHub commit/push ചെയ്താൽ frontend deploy ആകും.
6. Browser-ൽ Ctrl+Shift+R നൽകി വീണ്ടും login ചെയ്യുക.

ഈ patch session token പരിശോധിച്ചശേഷം മാത്രമാണ് sessionCheck success നൽകുന്നത്. Authentication ഒഴിവാക്കുന്നില്ല. Google Sheets ഡാറ്റ മാറ്റുന്നില്ല.
