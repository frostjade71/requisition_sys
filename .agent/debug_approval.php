<?php
/**
 * Debug script to test approval process
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Approval System Debug</h2>";

// Check if user is logged in
echo "<h3>Session Information:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Get all requisition requests
echo "<h3>Requisition Requests:</h3>";
$stmt = $pdo->query("SELECT * FROM requisition_requests");
$requests = $stmt->fetchAll();
echo "<pre>";
print_r($requests);
echo "</pre>";

// Get all approvals
echo "<h3>Approvals:</h3>";
$stmt = $pdo->query("SELECT * FROM approvals ORDER BY requisition_id, approval_level");
$approvals = $stmt->fetchAll();
echo "<pre>";
print_r($approvals);
echo "</pre>";

// Test approval update
echo "<h3>Test Approval Update:</h3>";
$requisition_id = 5;
$approval_level = 1;

// Check current approval record
$stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :req_id AND approval_level = :level");
$stmt->execute(['req_id' => $requisition_id, 'level' => $approval_level]);
$approval = $stmt->fetch();

echo "<p>Current approval record for requisition_id=$requisition_id, level=$approval_level:</p>";
echo "<pre>";
print_r($approval);
echo "</pre>";

// Try to update it
echo "<h3>Attempting Update:</h3>";
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
    'status' => 'approved',
    'approver_name' => 'Test User',
    'remarks' => 'Test remarks',
    'requisition_id' => $requisition_id,
    'approval_level' => $approval_level
]);

echo "<p>Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
echo "<p>Rows affected: " . $stmt->rowCount() . "</p>";

// Check updated record
$stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :req_id AND approval_level = :level");
$stmt->execute(['req_id' => $requisition_id, 'level' => $approval_level]);
$approval = $stmt->fetch();

echo "<p>Updated approval record:</p>";
echo "<pre>";
print_r($approval);
echo "</pre>";
