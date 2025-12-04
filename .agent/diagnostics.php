<?php
// Start session and load dependencies FIRST, before any HTML output
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

$resetMessage = '';

// Handle reset action
if (isset($_POST['reset'])) {
    try {
        $pdo->exec("UPDATE requisition_requests SET status = 'pending', current_approval_level = 1 WHERE id = 6");
        $pdo->exec("UPDATE approvals SET status = 'pending', approver_name = NULL, remarks = NULL, approved_at = NULL WHERE requisition_id = 6");
        $resetMessage = '<div class="success">✓ Test data has been reset! Request #6 is now at Level 1, Pending.</div>';
    } catch (Exception $e) {
        $resetMessage = '<div class="error">✗ Reset failed: ' . $e->getMessage() . '</div>';
    }
}

// Fetch request data
$stmt = $pdo->query("SELECT * FROM requisition_requests WHERE id = 6");
$request = $stmt->fetch();

// Fetch approvals data
$stmt = $pdo->query("SELECT * FROM approvals WHERE requisition_id = 6 ORDER BY approval_level");
$approvals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approval System Diagnostics</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 1400px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; background: #fff; padding: 15px; border-left: 4px solid #2196F3; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f5f5f5; }
        .status-pending { background: #FFF3CD; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-approved { background: #D4EDDA; color: #155724; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-rejected { background: #F8D7DA; color: #721C24; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .highlight { background: #FFFFCC; font-weight: bold; }
        .error { background: #F8D7DA; color: #721C24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .success { background: #D4EDDA; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #D1ECF1; color: #0C5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2196F3; color: white; }
        .btn-success { background: #4CAF50; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <h1>🔍 Approval System Diagnostics</h1>
    <p style="color: #666;">Generated at: <?php echo date('Y-m-d H:i:s'); ?></p>

    <?php echo $resetMessage; ?>

    <div class="section">
        <h2>📋 Session Information</h2>
        <?php if (isLoggedIn()): ?>
            <div class="success">✓ User is logged in</div>
            <table>
                <tr><th>Key</th><th>Value</th></tr>
                <tr><td>User ID</td><td class="highlight"><?php echo getCurrentUserId(); ?></td></tr>
                <tr><td>User Name</td><td class="highlight"><?php echo $_SESSION['user_name'] ?? 'N/A'; ?></td></tr>
                <tr><td>User Email</td><td><?php echo $_SESSION['user_email'] ?? 'N/A'; ?></td></tr>
                <tr><td>Approval Level</td><td class="highlight"><?php echo getCurrentUserLevel() ?? 'N/A'; ?></td></tr>
                <tr><td>Is Admin</td><td class="highlight"><?php echo isAdmin() ? 'YES' : 'NO'; ?></td></tr>
            </table>
        <?php else: ?>
            <div class="error">✗ No user is logged in. Please <a href="../login.php">login</a> first.</div>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>📝 Requisition Request #6</h2>
        <?php if ($request): ?>
            <table>
                <tr><th>Field</th><th>Value</th></tr>
                <tr><td>ID</td><td><?php echo $request['id']; ?></td></tr>
                <tr><td>RF Control Number</td><td><strong><?php echo $request['rf_control_number']; ?></strong></td></tr>
                <tr><td>Requester Name</td><td><?php echo $request['requester_name']; ?></td></tr>
                <tr><td>Department</td><td><?php echo $request['department']; ?></td></tr>
                <tr class="highlight"><td><strong>Status</strong></td><td><span class="status-<?php echo $request['status']; ?>"><?php echo strtoupper($request['status']); ?></span></td></tr>
                <tr class="highlight"><td><strong>Current Approval Level</strong></td><td><strong style="font-size: 18px;">Level <?php echo $request['current_approval_level']; ?> of 5</strong></td></tr>
                <tr><td>Created At</td><td><?php echo $request['created_at']; ?></td></tr>
                <tr><td>Updated At</td><td><?php echo $request['updated_at']; ?></td></tr>
            </table>
        <?php else: ?>
            <div class="error">Request #6 not found in database!</div>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>⏱️ Approval Timeline for Request #6</h2>
        <table>
            <tr><th>Level</th><th>Role</th><th>Status</th><th>Approver Name</th><th>Approved At</th><th>Remarks</th></tr>
            <?php foreach ($approvals as $approval): ?>
            <tr <?php if ($request && $approval['approval_level'] == $request['current_approval_level']) echo 'class="highlight"'; ?>>
                <td><strong>Level <?php echo $approval['approval_level']; ?></strong></td>
                <td><?php echo $approval['approver_role']; ?></td>
                <td><span class="status-<?php echo $approval['status']; ?>"><?php echo strtoupper($approval['status']); ?></span></td>
                <td><?php echo $approval['approver_name'] ?: '-'; ?></td>
                <td><?php echo $approval['approved_at'] ?: '-'; ?></td>
                <td><?php echo $approval['remarks'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p style="color: #666;"><em>Highlighted row = Current approval level</em></p>
    </div>

    <div class="section">
        <h2>🧪 Test Actions</h2>
        <form method="POST" style="margin: 20px 0;">
            <button type="submit" name="reset" class="btn btn-danger" onclick="return confirm('Reset test data to Level 1, Pending?')">🔄 Reset Test Data</button>
        </form>
        <div class="info">
            <strong>Testing Steps:</strong>
            <ol>
                <li>Make sure you're logged in as an approver or admin</li>
                <li>Note the current approval level above (highlighted row)</li>
                <li>Open the browser console (F12) to see debug logs</li>
                <li>Go to <a href="../approver/view_request.php?id=6" target="_blank">View Request Page</a></li>
                <li>Click "Approve Request" button</li>
                <li>Check the console for debug output</li>
                <li>Come back here and refresh to see if the level changed</li>
            </ol>
        </div>
    </div>

    <div class="section">
        <h2>🔐 Test Credentials</h2>
        <table>
            <tr><th>Email</th><th>Password</th><th>Role</th><th>Level</th><th>Can Approve</th></tr>
            <tr><td>juan.delacruz@leyeco3.com</td><td>password</td><td>Section Head</td><td>1</td><td>Level 1 requests</td></tr>
            <tr><td>maria.santos@leyeco3.com</td><td>password</td><td>Warehouse Section Head</td><td>2</td><td>Level 2 requests</td></tr>
            <tr><td>pedro.reyes@leyeco3.com</td><td>password</td><td>Budget Officer</td><td>3</td><td>Level 3 requests</td></tr>
            <tr><td>ana.garcia@leyeco3.com</td><td>password</td><td>Internal Auditor</td><td>4</td><td>Level 4 requests</td></tr>
            <tr><td>roberto.fernandez@leyeco3.com</td><td>password</td><td>General Manager</td><td>5</td><td>Level 5 requests</td></tr>
            <tr style="background: #E8F5E9;"><td><strong>admin@leyeco3.com</strong></td><td><strong>password</strong></td><td><strong>System Administrator</strong></td><td><strong>5 (Admin)</strong></td><td><strong>ANY level</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>🔗 Quick Links</h2>
        <a href="../approver/view_request.php?id=6" class="btn btn-primary" target="_blank">View Request #6</a>
        <a href="../approver/dashboard.php" class="btn btn-primary" target="_blank">Approver Dashboard</a>
        <a href="../login.php" class="btn btn-success">Login Page</a>
        <a href="javascript:location.reload()" class="btn btn-success">Refresh This Page</a>
    </div>

</body>
</html>
