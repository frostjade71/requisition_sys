<?php
/**
 * Test Approval API Call
 * Directly test the approval process
 */

header('Content-Type: application/json');

// Load configuration first (before session starts)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session after configuration is loaded
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulate the approval request
$test_data = [
    'requisition_id' => 5,
    'approval_level' => 1,
    'action' => 'approved',
    'remarks' => 'Test approval'
];

echo "=== Testing Approval Process ===\n\n";
echo "Session Info:\n";
echo "- User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "- User Name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "\n";
echo "- Approval Level: " . ($_SESSION['approval_level'] ?? 'NOT SET') . "\n\n";

echo "Test Data:\n";
echo json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        die("ERROR: Database connection failed\n");
    }
    
    echo "✓ Database connected\n\n";
    
    // Check current approval record
    echo "--- Current Approval Record ---\n";
    $stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :id AND approval_level = :level");
    $stmt->execute(['id' => $test_data['requisition_id'], 'level' => $test_data['approval_level']]);
    $current = $stmt->fetch();
    
    if ($current) {
        echo json_encode($current, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "ERROR: No approval record found!\n\n";
    }
    
    // Try to update
    echo "--- Attempting Update ---\n";
    $stmt = $pdo->prepare("
        UPDATE approvals 
        SET status = :status,
            approver_name = :approver_name,
            remarks = :remarks,
            approved_at = NOW()
        WHERE requisition_id = :requisition_id 
        AND approval_level = :approval_level
    ");
    
    $result = $stmt->execute([
        'status' => $test_data['action'],
        'approver_name' => $_SESSION['user_name'],
        'remarks' => $test_data['remarks'],
        'requisition_id' => $test_data['requisition_id'],
        'approval_level' => $test_data['approval_level']
    ]);
    
    $rowsAffected = $stmt->rowCount();
    
    echo "Update Result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    echo "Rows Affected: {$rowsAffected}\n\n";
    
    // Check updated record
    echo "--- Updated Approval Record ---\n";
    $stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :id AND approval_level = :level");
    $stmt->execute(['id' => $test_data['requisition_id'], 'level' => $test_data['approval_level']]);
    $updated = $stmt->fetch();
    
    if ($updated) {
        echo json_encode($updated, JSON_PRETTY_PRINT) . "\n\n";
    }
    
    // Rollback to keep database clean
    echo "--- Rolling Back (Test Mode) ---\n";
    $stmt = $pdo->prepare("
        UPDATE approvals 
        SET status = 'pending',
            approver_name = NULL,
            remarks = NULL,
            approved_at = NULL
        WHERE requisition_id = :requisition_id 
        AND approval_level = :approval_level
    ");
    $stmt->execute([
        'requisition_id' => $test_data['requisition_id'],
        'approval_level' => $test_data['approval_level']
    ]);
    echo "✓ Rolled back changes\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
