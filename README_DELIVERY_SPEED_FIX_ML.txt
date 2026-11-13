SM LAUNDRY – DELIVERY SAVE SPEED FIX

What changed
1. Delivery screen no longer calls loadOrdersFromDb() after saving.
2. The delivery list is updated locally after the API confirms success.
3. Save button shows a spinner and "Saving Delivery..." while the request is running.
4. Duplicate clicks are blocked while a save is in progress.
5. Added google-apps-script/DELIVERY_SPEED_PATCH.txt with the backend batch-update block.

IMPORTANT BACKEND STEP
The uploaded project ZIP does not contain the live Google Apps Script Code.gs.
Open the existing Apps Script project and, inside routePost(a,d), replace its current
if(a==='updateDeliveryCollection'){...} block with the code in:
google-apps-script/DELIVERY_SPEED_PATCH.txt

Save and deploy the Apps Script as a NEW VERSION. Keep the same deployment URL.
Do not replace the whole Code.gs with the patch file; it is only the block to replace.

FRONTEND DEPLOY
Replace the repository files with the contents of this ZIP and commit to GitHub.
Cloudflare Pages should deploy from the connected repository automatically.

Cloudflare Pages settings remain:
- Build command: blank
- Deploy command: blank
- Preview command: blank
- Build output directory: public
- Root directory: blank

No Google Sheets data or sheet structure is changed.
