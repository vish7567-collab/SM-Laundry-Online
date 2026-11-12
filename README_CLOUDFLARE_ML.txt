SM LAUNDRY - CLOUDFLARE PAGES + GOOGLE SHEETS

This version keeps Google Sheets as the database. Cloudflare Pages Functions proxy requests to the Google Apps Script Web App.

IMPORTANT
- Do not configure DATABASE_URL, JWT_SECRET, ADMIN_USERNAME or ADMIN_PASSWORD_HASH.
- Google Apps Script URL is configured in functions/_gas.js.
- Deploy the Apps Script as a Web App, Execute as: Me, access: Anyone (or Anyone with the link, depending on the Google account policy).
- The Apps Script project must contain the supplied Code.gs and the spreadsheet must have the expected sheets: Orders, Collections, Institutions, Rates, Users.
- Cloudflare Pages build output directory: public
- Root directory: blank
- Build command: leave blank (there are no npm dependencies in this proxy version). If your dashboard currently has npm install configured, it is harmless but unnecessary.
- Functions directory: functions (at project root).

LOGIN AND SESSION
The browser uses a Secure, HttpOnly, SameSite=Lax cookie containing the Apps Script session token. The Apps Script cache controls the 10-minute inactivity timeout. Do not expose the token in page JavaScript.

DEPLOYMENT
Commit all files from this ZIP to the GitHub repository root, replacing matching files. Keep public/ and functions/ at the repository root. Cloudflare Pages will deploy automatically from main.

TEST
Open /health. It should return JSON identifying Google Apps Script + Google Sheets. Then open /login.html and sign in using the existing username/password stored in the Users sheet.

Do not delete or recreate the existing Google Spreadsheet. This proxy does not migrate or modify spreadsheet data.
