SM LAUNDRY ONLINE – NETLIFY + NEON
==================================

ഈ പാക്കേജ് SM Laundry-യുടെ നിലവിലുള്ള UI നിലനിർത്തി Netlify + Neon PostgreSQL-ൽ പ്രവർത്തിപ്പിക്കാനുള്ള തയ്യാറാക്കിയ പ്രോജക്ടാണ്. പഴയ ഡാറ്റാബേസിലെ റെക്കോർഡുകൾ ഈ പാക്കേജിൽ ഉൾപ്പെടുത്തിയിട്ടില്ല.

ഫോൾഡർ ഘടന
- public/index.html : പ്രധാന SM Laundry ആപ്ലിക്കേഷൻ
- public/login.html : Admin Login
- public/assets/mtc_letterhead.png : DCB പ്രിന്റ് ലെറ്റർഹെഡ്
- netlify/functions/api.js : Orders, Collections, Rates, Institutions, DCB API
- netlify/functions/auth.js : HttpOnly cookie authentication
- netlify/functions/health.js : Database connection test
- schema_neon_empty.sql : റെക്കോർഡുകളില്ലാത്ത PostgreSQL table schema

GitHub / Netlify Deploy
1. GitHub-ൽ സ്വകാര്യ repository `SM-Laundry-Online` തുറക്കുക.
2. ഈ ZIP extract ചെയ്ത് ഉള്ളിലെ `SM_Laundry_Netlify` ഫോൾഡറിലെ ഫയലുകൾ repository-യുടെ root-ലേക്ക് upload ചെയ്യുക. `public`, `netlify`, `netlify.toml`, `package.json`, `schema_neon_empty.sql` എന്നിവ root-ൽ തന്നെ വേണം.
3. GitHub → Commit changes ചെയ്യുക.
4. Netlify-ൽ Add new project → Import an existing project → GitHub → `SM-Laundry-Online` തിരഞ്ഞെടുക്കുക.
5. Build command ഒഴിച്ചിടുക; Publish directory `public` ആയിരിക്കണം. Netlify.toml ഇതും Functions path-ഉം ക്രമീകരിക്കുന്നു.
6. Netlify Site configuration → Environment variables-ൽ താഴെയുള്ളവ ചേർക്കുക:
   DATABASE_URL = Neon → Connect → Pooled connection string (മുഴുവൻ URI)
   JWT_SECRET = നീളമുള്ള random secret (കുറഞ്ഞത് 32 bytes)
   ADMIN_USERNAME = നിങ്ങൾ തിരഞ്ഞെടുക്കുന്ന Admin username
   ADMIN_PASSWORD_HASH = bcryptjs hash മാത്രം; plain password അല്ല
7. Deploy ചെയ്യുക. Deploy കഴിഞ്ഞ് `https://YOUR-SITE/.netlify/functions/health` തുറന്ന് database=connected ആണെന്ന് പരിശോധിക്കുക.
8. `https://YOUR-SITE/.netlify/functions/auth` GET request login ഇല്ലാതെ 401 നൽകണം; login പേജിൽ നിന്ന് Admin credentials ഉപയോഗിച്ച് പ്രവേശിക്കുക.

Neon Database
- Neon-ൽ നിലവിലുള്ള schema ഇതിനകം തയ്യാറാക്കിയിട്ടുണ്ടെങ്കിൽ schema_neon_empty.sql വീണ്ടും Run ചെയ്യരുത്.
- പുതിയ ശൂന്യമായ database ആണെങ്കിൽ മാത്രം schema_neon_empty.sql ഒരിക്കൽ Run ചെയ്യുക.
- ഈ SQL-ൽ പഴയ orders/customers/collections/rates റെക്കോർഡുകൾ ഒന്നും ഇല്ല.

Admin password hash ഉണ്ടാക്കൽ
1. കമ്പ്യൂട്ടറിൽ Node.js ഇൻസ്റ്റാൾ ചെയ്യുക.
2. project folder-ൽ terminal തുറന്ന് `npm install` നടത്തുക.
3. താഴെയുള്ള command-ൽ YOUR_NEW_PASSWORD എന്നത് നിങ്ങളുടെ പുതിയ password ഉപയോഗിച്ച് മാറ്റുക:
   node -e "const b=require('bcryptjs'); console.log(b.hashSync('YOUR_NEW_PASSWORD',12))"
4. ലഭിക്കുന്ന hash മാത്രം Netlify-യിലെ ADMIN_PASSWORD_HASH ആയി നൽകുക. Password GitHub-ലോ ഇവിടെ ChatGPT-ലോ അയക്കരുത്.

സുരക്ഷയും പ്രവർത്തനവും
- Login cookie HttpOnly, Secure, SameSite=Lax ആണ്.
- 10 മിനിറ്റ് user inactivity കഴിഞ്ഞാൽ automatic logout.
- Server token-നും 10 മിനിറ്റ് expiry ഉണ്ട്; API activity-യിൽ പുതുക്കും.
- DCB Report-ൽ Opening Balance, Total Debit, Total Credit, Closing Balance കാണിക്കും. ഈ റിപ്പോർട്ട് ലഭ്യമായ മുഴുവൻ ഇടപാടുകളും ഉൾക്കൊള്ളുന്നതിനാൽ opening balance തുടക്കത്തിൽ 0 ആണ്.
- എല്ലാ database query-കളും parameterized ആണ്. PostgreSQL camelCase fields (`isoDate`, `extraCharge`) quoted ആയി ഉപയോഗിച്ചിട്ടുണ്ട്.

പരിശോധന
- ഈ runtime-ൽ JavaScript syntax checks വിജയിച്ചു. Netlify/Neon credentials ഇല്ലാത്തതിനാൽ live deployment, login, CRUD, PDF/Excel/print എന്നിവയുടെ end-to-end test ഇവിടെ നടത്താനായിട്ടില്ല. Deploy ചെയ്തശേഷം health check വിജയിക്കണം; തുടർന്ന് എല്ലാ മെനുകളും പരിശോധിക്കുക.
