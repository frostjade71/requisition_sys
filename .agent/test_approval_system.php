<!DOCTYPE html>
<html>
<head>
    <title>Test Approval System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-section {
            background: #f5f5f5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .btn {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }
        .btn-success { background: #4CAF50; color: white; }
        .btn-danger { background: #f44336; color: white; }
    </style>
</head>
<body>
    <h1>Approval System Test</h1>
    
    <?php
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
    
    // Handle test actions
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'reset') {
            // Reset test data
            $pdo->exec("UPDATE requisition_requests SET status = 'pending', current_approval_level = 1 WHERE id = 5");
            $pdo->exec("UPDATE approvals SET status = 'pending', approver_name = NULL, remarks = NULL, approved_at = NULL WHERE requisition_id = 5");
            echo '<div class="success">✓ Test data reset successfully!</div>';
        }
    }
    ?>
    
    <div class="test-section">
        <h2>Current Request Status</h2>
        <?php
        $stmt = $pdo->query("SELECT * FROM requisition_requests WHERE id = 5");
        $request = $stmt->fetch();
        ?>
        <table>
            <tr>
                <th>RF Number</th>
                <th>Status</th>
                <th>Current Level</th>
                <th>Requester</th>
                <th>Department</th>
            </tr>
            <tr>
                <td><?php echo $request['rf_control_number']; ?></td>
                <td><strong><?php echo strtoupper($request['status']); ?></strong></td>
                <td><strong>Level <?php echo $request['current_approval_level']; ?></strong></td>
                <td><?php echo $request['requester_name']; ?></td>
                <td><?php echo $request['department']; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="test-section">
        <h2>Approval Timeline</h2>
        <?php
        $stmt = $pdo->query("SELECT * FROM approvals WHERE requisition_id = 5 ORDER BY approval_level");
        $approvals = $stmt->fetchAll();
        ?>
        <table>
            <tr>
                <th>Level</th>
                <th>Role</th>
                <th>Status</th>
                <th>Approver</th>
                <th>Approved At</th>
                <th>Remarks</th>
            </tr>
            <?php foreach ($approvals as $approval): ?>
            <tr>
                <td><?php echo $approval['approval_level']; ?></td>
                <td><?php echo $approval['approver_role']; ?></td>
                <td><strong><?php echo strtoupper($approval['status']); ?></strong></td>
                <td><?php echo $approval['approver_name'] ?: '-'; ?></td>
                <td><?php echo $approval['approved_at'] ?: '-'; ?></td>
                <td><?php echo $approval['remarks'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="test-section">
        <h2>Test Actions</h2>
        <form method="POST">
            <button type="submit" name="action" value="reset" class="btn btn-danger">
                Reset Test Data (Set to Level 1, Pending)
            </button>
        </form>
        
        <p class="info">
            <strong>To test the approval:</strong><br>
            1. Click "Reset Test Data" above<br>
            2. Go to <a href="../approver/view_request.php?id=5" target="_blank">View Request Page</a><br>
            3. Login as an approver (or admin)<br>
            4. Click "Approve Request"<br>
            5. Come back here and refresh to see the changes
        </p>
    </div>
    
    <div class="test-section">
        <h2>Session Info</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div class="test-section">
        <h2>Test Credentials</h2>
        <table>
            <tr>
                <th>Email</th>
                <th>Password</th>
                <th>Role</th>
                <th>Level</th>
            </tr>
            <tr>
                <td>juan.delacruz@leyeco3.com</td>
                <td>password</td>
                <td>Section Head</td>
                <td>1</td>
            </tr>
            <tr>
                <td>maria.santos@leyeco3.com</td>
                <td>password</td>
                <td>Warehouse Section Head</td>
                <td>2</td>
            </tr>
            <tr>
                <td>admin@leyeco3.com</td>
                <td>password</td>
                <td>Admin</td>
                <td>5 (Can approve any level)</td>
            </tr>
        </table>
    </div>
</body>
</html>
