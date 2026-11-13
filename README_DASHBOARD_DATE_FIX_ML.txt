SM LAUNDRY — DASHBOARD + DATE FORMAT FIX

പരിഹാരങ്ങൾ:
1. Today's Overview-ൽ ഇന്ന് എന്ന തീയതി UTC ആയി കണക്കാക്കുന്ന പ്രശ്നം പരിഹരിച്ചു. Asia/Kolkata local calendar date ഉപയോഗിക്കുന്നു.
2. 7 ദിവസത്തെ Orders chart-ൽ UTC toISOString() മൂലമുള്ള ഒരു ദിവസത്തെ shift പരിഹരിച്ചു.
3. Google Sheets Date cell JSON ആയി ISO timestamp വരുമ്പോൾ frontend അത് IST തീയതിയാക്കി normalize ചെയ്യുന്നു.
4. Edit Order സേവ് ചെയ്യുമ്പോൾ പഴയ isoDate/time തെറ്റായ ISO timestamp ആയി വീണ്ടും സേവ് ചെയ്യില്ല.
5. Code.gs-ൽ date, isoDate, time എന്നിവ text format ആയി എഴുതി സ്ഥിരമായ dd/MM/yyyy, yyyy-MM-dd, HH:mm:ss format ഉറപ്പാക്കുന്നു.
6. മുമ്പത്തെ Delivery Speed Fix ഉൾപ്പെടുത്തിയിരിക്കുന്നു.

DEPLOY:
A) GitHub/Cloudflare: ZIP-ലെ SM-Laundry-Online-main ഫോൾഡറിലുള്ള ഫയലുകൾ repo root-ലേക്ക് replace ചെയ്ത് commit/push ചെയ്യുക. Cloudflare Pages auto deploy ചെയ്യണം. Build command blank, output directory public തന്നെയായിരിക്കണം.
B) Google Apps Script: google-apps-script/Code.gs ആണ് complete backend file. നിലവിലെ Apps Script-ൽ പിന്നീട് നടത്തിയ വേറിട്ട മാറ്റങ്ങൾ ഉണ്ടെങ്കിൽ അവ നഷ്ടപ്പെടാതിരിക്കാൻ ആദ്യം backup എടുക്കുക; തുടർന്ന് ഈ Code.gs-ലെ മാറ്റങ്ങൾ merge ചെയ്യുക. Save → Deploy → Manage deployments → Edit → New version → Deploy. നിലവിലെ Web App URL മാറ്റേണ്ടതില്ല.

DATA: Existing Sheets/data delete ചെയ്യുകയോ പുതിയ spreadsheet സൃഷ്ടിക്കുകയോ വേണ്ട.

പരിശോധന: JavaScript syntax validation നടത്തി. Live Google Apps Script/Cloudflare deployment ഇവിടെ നിന്ന് നടത്തുകയോ live data update പരിശോധിക്കുകയോ ചെയ്തിട്ടില്ല. Deploy കഴിഞ്ഞ് ഇന്ന് ഒരു test order സേവ് ചെയ്യുക, പഴയ order edit ചെയ്ത് save ചെയ്യുക, Orders sheet-ൽ date/isoDate/time format പരിശോധിക്കുക.
