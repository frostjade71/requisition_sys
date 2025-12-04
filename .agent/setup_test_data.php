<?php
/**
 * Setup Test Data for Approval System
 * This script ensures Request #5 exists with proper approval records
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Test Data</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        .success { background: #D4EDDA; color: #155724; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #F8D7DA; color: #721C24; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #D1ECF1; color: #0C5460; padding: 15px; margin: 10px 0; border-radius: 4px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Setup Test Data for Approval System</h1>
";

try {
    // Check if request #5 exists
    $stmt = $pdo->query("SELECT * FROM requisition_requests WHERE id = 5");
    $request = $stmt->fetch();
    
    if (!$request) {
        echo "<div class='info'>Request #5 does not exist. Creating it now...</div>";
        
        // Create request #5
        $stmt = $pdo->prepare("
            INSERT INTO requisition_requests 
            (id, rf_control_number, requester_name, department, purpose, status, current_approval_level, created_at, updated_at)
            VALUES 
            (5, 'RF-20251204-0001', 'Jaderby', 'Finance Department', 'needed for construction', 'pending', 1, NOW(), NOW())
        ");
        $stmt->execute();
        
        echo "<div class='success'>✓ Created requisition request #5</div>";
        
        // Create approval records
        $approvalRoles = [
            1 => 'Recommending Approval - Section Head/Div. Head/Department Head',
            2 => 'Inventory Checked - Warehouse Section Head',
            3 => 'Budget Approval - Div. Supervisor/Budget Officer',
            4 => 'Checked By - Internal Auditor',
            5 => 'Approved By - General Manager'
        ];
        
        foreach ($approvalRoles as $level => $role) {
            $stmt = $pdo->prepare("
                INSERT INTO approvals 
                (requisition_id, approval_level, approver_role, status, created_at, updated_at)
                VALUES 
                (5, :level, :role, 'pending', NOW(), NOW())
            ");
            $stmt->execute([
                'level' => $level,
                'role' => $role
            ]);
        }
        
        echo "<div class='success'>✓ Created 5 approval records for request #5</div>";
        
        // Create a requisition item
        $stmt = $pdo->prepare("
            INSERT INTO requisition_items 
            (requisition_id, quantity, unit, description, warehouse_inventory, balance_for_purchase, remarks, created_at)
            VALUES 
            (5, 1, 'boxes', 'electrical tape', 0, 0, 'Wirings', NOW())
        ");
        $stmt->execute();
        
        echo "<div class='success'>✓ Created requisition item for request #5</div>";
        
    } else {
        echo "<div class='success'>✓ Request #5 already exists</div>";
        
        // Reset it to pending, level 1
        $pdo->exec("UPDATE requisition_requests SET status = 'pending', current_approval_level = 1, updated_at = NOW() WHERE id = 5");
        $pdo->exec("UPDATE approvals SET status = 'pending', approver_name = NULL, remarks = NULL, approved_at = NULL WHERE requisition_id = 5");
        
        echo "<div class='success'>✓ Reset request #5 to Level 1, Pending</div>";
    }
    
    // Show current state
    echo "<h2>Current State of Request #5</h2>";
    
    $stmt = $pdo->query("SELECT * FROM requisition_requests WHERE id = 5");
    $request = $stmt->fetch();
    
    echo "<pre>";
    print_r($request);
    echo "</pre>";
    
    echo "<h2>Approval Records</h2>";
    $stmt = $pdo->query("SELECT * FROM approvals WHERE requisition_id = 5 ORDER BY approval_level");
    $approvals = $stmt->fetchAll();
    
    echo "<pre>";
    print_r($approvals);
    echo "</pre>";
    
    echo "<div class='success'>";
    echo "<h3>✓ Setup Complete!</h3>";
    echo "<p>Request #5 is ready for testing.</p>";
    echo "<p><a href='diagnostics.php'>Go to Diagnostics Page</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
