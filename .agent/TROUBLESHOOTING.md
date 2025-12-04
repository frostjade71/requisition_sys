# Approval System Troubleshooting Guide

## Issue
When clicking "Approve" in `approver/view_request.php`, the Timeline Approval section and `requisition_requests` table are not updating.

## Fix Applied
Changed line 169 in `approver/view_request.php` from:
```php
<input type="hidden" name="approval_level" value="<?php echo $user_level; ?>">
```
To:
```php
<input type="hidden" name="approval_level" value="<?php echo $request['current_approval_level']; ?>">
```

## Debugging Steps

### Step 1: Access the Diagnostics Page
Navigate to: `http://localhost/requisition_sys/.agent/diagnostics.php`

This page shows:
- Current session information (who you're logged in as)
- Request #5 current status and level
- Complete approval timeline
- Ability to reset test data

### Step 2: Check Browser Console
1. Open the browser console (Press F12)
2. Go to the Console tab
3. Navigate to `approver/view_request.php?id=5`
4. Click "Approve Request"
5. Look for the debug logs that start with `=== APPROVAL DEBUG ===`

You should see:
```
=== APPROVAL DEBUG ===
Action: approved
Data being sent: {requisition_id: "5", approval_level: "1", action: "approved", remarks: ""}
Requisition ID: 5 Type: string
Approval Level: 1 Type: string
Sending request to: /requisition_sys/api/process_approval.php
Response received: {success: true, message: "..."}
Success: true
Message: Request approved successfully. Moved to Level 2
Approval successful, reloading in 1.5s...
```

### Step 3: Test the API Directly
Navigate to: `http://localhost/requisition_sys/.agent/test_api.php`

This allows you to:
- Send approval requests directly to the API
- See the exact JSON response
- Test without the full page interface

### Step 4: Check Database Directly
Run these SQL queries in phpMyAdmin:

```sql
-- Check request status
SELECT id, rf_control_number, status, current_approval_level, updated_at 
FROM requisition_requests 
WHERE id = 5;

-- Check approval timeline
SELECT approval_level, status, approver_name, approved_at, remarks 
FROM approvals 
WHERE requisition_id = 5 
ORDER BY approval_level;
```

## Common Issues and Solutions

### Issue 1: "No approval record found to update"
**Cause:** The approval_level being sent doesn't match any record in the approvals table.

**Solution:** 
- Check that approval records exist for the request
- Verify the approval_level matches the request's current_approval_level
- Run: `SELECT * FROM approvals WHERE requisition_id = 5 AND approval_level = 1;`

### Issue 2: "Request is not pending"
**Cause:** The request status is not 'pending' (might be 'approved', 'rejected', or 'completed').

**Solution:**
- Reset the request: `UPDATE requisition_requests SET status = 'pending', current_approval_level = 1 WHERE id = 5;`
- Reset approvals: `UPDATE approvals SET status = 'pending', approver_name = NULL, approved_at = NULL WHERE requisition_id = 5;`

### Issue 3: "You are not authorized to approve this level"
**Cause:** User's approval level doesn't match the request's current level (and user is not admin).

**Solution:**
- Login as the correct level approver
- OR login as admin (admin@leyeco3.com)
- Check: `SELECT approval_level FROM approvers WHERE id = [your_user_id];`

### Issue 4: Page reloads but nothing changes
**Cause:** The update might be failing silently, or there's a caching issue.

**Solution:**
1. Check browser console for errors
2. Check network tab for the API response
3. Hard refresh the page (Ctrl+Shift+R)
4. Check the diagnostics page to see actual database state

### Issue 5: JavaScript errors in console
**Cause:** Missing dependencies (Ajax, Loading, Toast utilities).

**Solution:**
- Verify `assets/js/main.js` is loaded
- Check browser console for 404 errors
- Ensure BASE_URL is correctly defined

## Testing Workflow

### Complete Test Cycle:
1. **Reset Data** (diagnostics.php)
   - Click "Reset Test Data" button
   - Verify request is at Level 1, Pending

2. **Login as Level 1 Approver**
   - Email: juan.delacruz@leyeco3.com
   - Password: password

3. **Approve Request**
   - Go to view_request.php?id=5
   - Open browser console (F12)
   - Click "Approve Request"
   - Confirm the action

4. **Verify in Console**
   - Should see debug logs
   - Should see "Success: true"
   - Should see "Moved to Level 2"

5. **Verify in Diagnostics**
   - Refresh diagnostics.php
   - Request should now be at Level 2
   - Level 1 approval should show your name and timestamp

6. **Repeat for Other Levels**
   - Login as Level 2 approver (maria.santos@leyeco3.com)
   - Approve again
   - Continue through all 5 levels

## Files Modified
- ✅ `approver/view_request.php` - Fixed approval_level input (line 169)
- ✅ `approver/view_request.php` - Added console logging (lines 265-272, 278-280, 285-287, 291-292)

## Debug Files Created
- 📄 `.agent/diagnostics.php` - Main diagnostic dashboard
- 🧪 `.agent/test_api.php` - Direct API testing
- 🔍 `.agent/debug_approval.php` - Detailed API debugging
- 📋 `.agent/APPROVAL_FIX_GUIDE.md` - Original fix documentation

## Expected Flow

### When you click "Approve":
1. JavaScript collects form data
2. Sends POST request to `api/process_approval.php` with:
   ```json
   {
     "requisition_id": "5",
     "approval_level": "1",  // Current level of the request
     "action": "approved",
     "remarks": ""
   }
   ```
3. API validates user permissions
4. API updates `approvals` table:
   - Sets status = 'approved'
   - Sets approver_name = current user
   - Sets approved_at = NOW()
5. API updates `requisition_requests` table:
   - If level < 5: Increments current_approval_level
   - If level = 5: Sets status = 'approved'
6. API returns success response
7. Page reloads and shows updated timeline

## Still Not Working?

If the issue persists after following all steps:

1. **Check PHP Error Logs**
   - Location: `/var/log/apache2/error.log` (Docker)
   - Or check `api/show_logs.php`

2. **Enable Database Query Logging**
   - Add to `config/database.php`:
   ```php
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
   ```

3. **Test with Debug API**
   - Navigate to: `.agent/debug_approval.php`
   - Send a test approval
   - Check the detailed JSON response

4. **Verify Database Schema**
   - Ensure all tables exist
   - Check foreign key constraints
   - Verify column types match

5. **Clear All Caches**
   - Browser cache (Ctrl+Shift+Delete)
   - PHP opcache (restart Apache)
   - Session data (logout and login again)

## Contact Information
If you need further assistance, provide:
- Browser console logs (full output)
- Network tab response from `process_approval.php`
- Output from diagnostics.php
- PHP error logs
