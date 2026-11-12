SM LAUNDRY ONLINE – CLOUDFLARE PAGES + NEON
===========================================

ഈ പതിപ്പിൽ നിലവിലുള്ള SM Laundry UI നിലനിർത്തി Netlify Functions-നെ Cloudflare Pages Functions ആയി മാറ്റിയിട്ടുണ്ട്. Database Neon PostgreSQL തന്നെയാണ്. നിലവിലുള്ള orders/collection/rates/institutions ഡാറ്റ മാറ്റുകയോ ഇല്ലാതാക്കുകയോ ചെയ്യുന്ന SQL migration ഇതിൽ ഇല്ല.

പ്രധാന മാറ്റങ്ങൾ
- Login endpoint: /auth
- Orders / Collections / Rates / Institutions API: /api
- Database health check: /health
- Cloudflare Pages Functions: functions/ ഫോൾഡർ
- Static website: public/ ഫോൾഡർ
- Neon connection: @neondatabase/serverless

GitHub-ലേക്ക് അപ്‌ലോഡ് ചെയ്യുന്നതിന് മുമ്പ്
1. നിലവിലെ GitHub repository-യുടെ backup/ZIP സൂക്ഷിക്കുക.
2. ഈ ZIP extract ചെയ്യുക.
3. ഉള്ളിലെ SM-Laundry-Online-main ഫോൾഡറിലുള്ള എല്ലാ ഫയലുകളും GitHub repository root-ലേക്ക് upload ചെയ്യുക; പഴയ ഫയലുകൾ overwrite ചെയ്യുക. public, functions, netlify, package.json, wrangler.toml എന്നിവ root-ൽ വേണം.
4. GitHub-ൽ commit ചെയ്യുക.

Cloudflare Pages Build settings
- Framework preset: None
- Build command: npm install
- Build output directory: public
- Build output directory: public
- Root directory: (blank)
- Functions directory: functions (repository root-ൽ functions ഫോൾഡർ ഉണ്ടായിരിക്കണം)

Cloudflare Pages → Settings → Variables and Secrets → Production-ൽ താഴെപ്പറയുന്നവ ചേർക്കുക:
- DATABASE_URL = Neon-ൽ നിന്നുള്ള pooled connection string
- JWT_SECRET = കുറഞ്ഞത് 32 bytes ഉള്ള ശക്തമായ random secret
- ADMIN_USERNAME = നിലവിലെ Admin username
- ADMIN_PASSWORD_HASH = bcrypt hash (plain password അല്ല)
ഇവ Secrets ആയി ചേർക്കുക. നിലവിലെ Neon DB-യും Admin hash-ഉം അറിയാവുന്ന മൂല്യങ്ങളായി മാറ്റമില്ലാതെ നൽകുക. ഇവിടെ യഥാർത്ഥ രഹസ്യങ്ങൾ ഉൾപ്പെടുത്തിയിട്ടില്ല.

Deploy കഴിഞ്ഞ് പരിശോധിക്കുക
1. https://YOUR-SITE/health തുറക്കുക. database=connected വരണം.
2. https://YOUR-SITE/login.html തുറന്ന് Admin login ചെയ്യുക.
3. Login വിജയിച്ചാൽ dashboard തുറക്കണം.
4. Orders, collection, rates, institutions, DCB report എന്നിവ ഓരോന്നും പരിശോധിക്കുക.

ശ്രദ്ധിക്കുക
- ഈ ZIP-ൽ Neon database-ന്റെ credentials ഉൾപ്പെടുത്തിയിട്ടില്ല; Cloudflare-ൽ Secrets ആയി ചേർക്കണം.
- പഴയ Netlify functions source `netlify/functions/` ഫോൾഡറിൽ reference-നായി സൂക്ഷിച്ചിട്ടുണ്ട്; Cloudflare-ൽ പ്രവർത്തിക്കുന്നത് root `functions/` ആണ്.
- Neon-ലെ tables/columns മുൻപ് തയ്യാറാക്കിയ schema അനുസരിച്ചായിരിക്കണം. ഇവിടെ database data delete/replace ചെയ്യുന്നില്ല.
- Cloudflare deploy ചെയ്ത ശേഷം health check വിജയിക്കാത്ത പക്ഷം login പരീക്ഷിക്കാതെ ആദ്യം DATABASE_URL/Neon connectivity പരിശോധിക്കുക.
- ഈ runtime-ൽ live Neon credentials ഇല്ലാത്തതിനാൽ live database/login/CRUD പരിശോധന നടത്തിയിട്ടില്ല.
