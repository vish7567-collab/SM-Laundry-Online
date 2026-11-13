SM LAUNDRY - ORDER EDIT LOCK + LOGIN UI UPDATE (V8)

മാറ്റങ്ങൾ:
1. Delivered, Part Bill, Part Payment, Payment Completed സ്റ്റാറ്റസിലുള്ള ഓർഡറുകൾ Edit ചെയ്യുന്നത് തടഞ്ഞു.
2. Order list-ൽ Edit icon-ന് പകരം lock icon കാണിക്കും.
3. Order Details modal-ലും Edit action മറയ്ക്കുന്നു / Editing Locked കാണിക്കുന്നു.
4. Frontend editOrder() ഉം saveEditedOrder() ഉം സുരക്ഷാ പരിശോധന നടത്തുന്നു.
5. Google Apps Script Code.gs-ലെ editOrder backend route-ലും നിലവിലെ Orders sheet status പരിശോധിച്ച് locked status ആണെങ്കിൽ update നിരസിക്കുന്നു. API നേരിട്ട് വിളിച്ചാലും edit സാധ്യമല്ല.
6. Login page-ന് responsive premium green glass UI, Poppins + Manjari font, മെച്ചപ്പെടുത്തിയ form fields, show/hide password, loading state എന്നിവ ചേർത്തു. Authentication flow മാറ്റിയിട്ടില്ല.

DEPLOY:
- public/index.html, public/login.html എന്നിവ GitHub-ൽ commit/push ചെയ്യുക.
- google-apps-script/Code.gs നിലവിലെ Apps Script-ൽ merge/replace ചെയ്ത് Save ചെയ്യുക.
- Apps Script: Deploy > Manage deployments > Edit > New version > Deploy.
- നിലവിലെ GAS Web App URL മാറ്റരുത്.
- Browser-ൽ Ctrl+Shift+R നൽകി പരിശോധിക്കുക.

കുറിപ്പ്: Part Payment status-ും മുമ്പത്തെ നിലവിലുള്ള lock rule പ്രകാരം edit-locked ആയി തുടരും. Google Fonts ലഭ്യമല്ലാത്ത സാഹചര്യത്തിൽ sans-serif fallback ഉപയോഗിക്കും.
Syntax validation: frontend inline JavaScript, Code.gs എന്നിവ വിജയിച്ചു. Live deployment test നടത്തിയിട്ടില്ല.
