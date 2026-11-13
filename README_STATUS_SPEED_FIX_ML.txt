SM LAUNDRY - ORDER STATUS SPEED FIX
===================================

What changed
------------
1. Order status update no longer reloads the complete Orders list after every click.
2. Frontend updates the current in-memory order, order list, delivery list (when visible), and dashboard immediately after the API confirms success.
3. Google Apps Script updateStatus now locates the matching order ID in the ID column and writes only the Status cell. It no longer reads the entire Orders sheet and rewrites every field.
4. Duplicate clicks while an update is in progress are blocked, and the UI shows an update message.
5. Existing order fields, collections, amount, extra charge, and customer data are not changed by a status update.

Deployment (both parts are required)
------------------------------------
A. Frontend / Cloudflare Pages
- Replace the repository files with the contents of this ZIP and commit/push to GitHub.
- Cloudflare Pages should deploy automatically after the commit if Git integration is enabled.

B. Google Apps Script backend
- Open the existing Apps Script project connected to the existing SM Laundry Google Sheet.
- Replace Code.gs with google-apps-script/Code.gs from this ZIP.
- Save, then Deploy > Manage deployments > Edit the existing Web App deployment > select New version > Deploy.
- Keep the existing spreadsheet and deployment URL. Do not create a new spreadsheet or change sheet data.

Validation
----------
- JavaScript syntax checks passed for the frontend inline scripts and Code.gs (checked as JavaScript).
- No live end-to-end test or measured response-time benchmark was possible here.
- Test Pending -> Completed and Completed -> Pending on a non-delivered, non-payment-final order.
