<?php
/**
 * Quick Database Check for Request #6
 */

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

header('Content-Type: text/plain');

echo "=== DATABASE CHECK FOR REQUEST #6 ===\n\n";

// Check requisition_requests
echo "1. REQUISITION REQUEST:\n";
$stmt = $pdo->query("SELECT * FROM requisition_requests WHERE id = 6");
$request = $stmt->fetch();
if ($request) {
    print_r($request);
} else {
    echo "NOT FOUND!\n";
}

echo "\n\n2. APPROVAL RECORDS:\n";
$stmt = $pdo->query("SELECT * FROM approvals WHERE requisition_id = 6 ORDER BY approval_level");
$approvals = $stmt->fetchAll();
if ($approvals) {
    foreach ($approvals as $approval) {
        echo "Level {$approval['approval_level']}: {$approval['status']} - {$approval['approver_role']}\n";
    }
    echo "\nFull details:\n";
    print_r($approvals);
} else {
    echo "NO APPROVAL RECORDS FOUND!\n";
}

echo "\n\n3. CHECK IF APPROVALS EXIST FOR LEVEL 1:\n";
$stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = 6 AND approval_level = 1");
$stmt->execute();
$level1 = $stmt->fetch();
if ($level1) {
    echo "Level 1 approval exists:\n";
    print_r($level1);
} else {
    echo "LEVEL 1 APPROVAL NOT FOUND!\n";
}
