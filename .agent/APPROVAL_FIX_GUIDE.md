x`# Approval System Fix - Complete Guide

## Problem Identified

When approving a requisition request, the Timeline Approval section and `requisition_requests` table were not updating.

## Root Cause

The issue was in `approver/view_request.php` line 169. The form was sending the **user's approval level** instead of the **request's current approval level**.

### Example of the Bug:
- Admin user has `approval_level = 5`
- Request is at `current_approval_level = 1`
- Form sent `approval_level = 5` (user's level)
- API tried to update approval record for level 5 instead of level 1
- No rows were affected because level 5 approval was still pending
- Timeline didn't update because wrong record was targeted

## Fix Applied

**File:** `approver/view_request.php`
**Line:** 169

**Before:**
```php
<input type="hidden" name="approval_level" value="<?php echo $user_level; ?>">
```

**After:**
```php
<input type="hidden" name="approval_level" value="<?php echo $request['current_approval_level']; ?>">
```

## How It Works Now

1. User clicks "Approve" or "Reject" button
2. Form sends:
   - `requisition_id`: The request ID
   - `approval_level`: **The request's current approval level** (not user's level)
   - `action`: 'approved' or 'rejected'
   - `remarks`: Optional comments

3. API (`api/process_approval.php`) receives the data:
   - Validates user has permission (either their level matches OR they're admin)
   - Updates the approval record at the **correct level**
   - Updates the `requisition_requests` table:
     - If rejected: Sets status to 'rejected'
     - If approved and level < 5: Increments `current_approval_level`
     - If approved and level = 5: Sets status to 'approved'

4. Page reloads and shows updated timeline

## Testing Checklist

- [ ] Level 1 approver can approve level 1 requests
- [ ] Level 2 approver can approve level 2 requests
- [ ] Admin can approve any level
- [ ] Timeline updates immediately after approval
- [ ] Request status changes correctly
- [ ] Current approval level increments correctly
- [ ] Rejection works and sets status to 'rejected'
- [ ] Approver name and timestamp are recorded

## Related Files

- `approver/view_request.php` - Display and approval form
- `api/process_approval.php` - Backend approval processing
- `includes/functions.php` - Helper functions
- Database tables:
  - `requisition_requests` - Main request data
  - `approvals` - Approval timeline records
  - `approvers` - User accounts

## Database Flow

### approvals table:
```
requisition_id | approval_level | status   | approver_name | approved_at
----------------|----------------|----------|---------------|-------------
5              | 1              | approved | John Doe      | 2024-12-04...
5              | 2              | pending  | NULL          | NULL
5              | 3              | pending  | NULL          | NULL
5              | 4              | pending  | NULL          | NULL
5              | 5              | pending  | NULL          | NULL
```

### requisition_requests table:
```
id | status  | current_approval_level
---|---------|----------------------
5  | pending | 2
```

After level 1 approval, the request moves to level 2.
