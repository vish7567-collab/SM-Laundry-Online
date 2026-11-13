SM LAUNDRY - NEW ENTRY / ORDER SAVE SPEED FIX

ഈ പതിപ്പിലെ മാറ്റങ്ങൾ:
1. Service type മാറുമ്പോൾ ഓരോ service-നും വേർതിരിച്ച് sequential API request അയക്കുന്നതിന് പകരം എല്ലാ service rates-ഉം ഒരൊറ്റ listRates request-ൽ ലഭ്യമാക്കുന്നു.
2. Rates request in-flight ആണെങ്കിൽ ഒന്നിലധികം UI actions അതേ Promise share ചെയ്യും; ലഭിച്ചശേഷം page memory-യിൽ cache ചെയ്യും.
3. Order save button-ൽ spinner + 'സേവ് ചെയ്യുന്നു...' status കാണിക്കും. Save നടക്കുമ്പോൾ double-submit തടയും.
4. Save API വിജയിച്ച ഉടൻ customer bill തുറക്കുകയും Orders list local ആയി update ചെയ്യുകയും ചെയ്യും. മുഴുവൻ Orders sheet വീണ്ടും download ചെയ്യുന്നത് background-ൽ നടക്കുന്നതിനാൽ bill കാണാൻ അതിനായി കാത്തിരിക്കേണ്ടതില്ല.
5. Google Apps Script / Google Sheets schema മാറ്റിയിട്ടില്ല. നിലവിലുള്ള spreadsheet data സംരക്ഷിക്കപ്പെടും.

DEPLOY:
- ZIP extract ചെയ്യുക.
- Repository root-ൽ ഉള്ള ഫയലുകൾ GitHub-ൽ replace ചെയ്ത് commit ചെയ്യുക.
- Cloudflare Pages auto-deploy പൂർത്തിയാകാൻ കാത്തിരിക്കുക.
- Google Apps Script Code.gs മാറ്റേണ്ടതില്ല; ഈ speed fix frontend-only ആണ്.
- Build command blank, output directory public എന്ന നിലവിലെ Cloudflare settings നിലനിർത്തുക.

TEST:
- New Entry > Service type മാറ്റുക; item list വേഗത്തിൽ വരണം.
- Save ചെയ്യുമ്പോൾ spinner കാണണം; button വീണ്ടും click ചെയ്യാൻ കഴിയരുത്.
- Save success കഴിഞ്ഞാൽ bill ഉടൻ തുറക്കണം. Orders list background refresh വഴി server-ുമായി reconcile ചെയ്യും.

കുറിപ്പ്: Live environment-ൽ benchmark നടത്തിയിട്ടില്ല. യഥാർത്ഥ സമയം Cloudflare/GAS/Sheets latency അനുസരിച്ച് മാറും.
