<?php
/**
 * Debug Approval API
 * This script mimics the approval process with detailed logging
 */

header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug = [];
$debug['timestamp'] = date('Y-m-d H:i:s');
$debug['request_method'] = $_SERVER['REQUEST_METHOD'];

// Check authentication
if (!isLoggedIn()) {
    $debug['error'] = 'Not logged in';
    $debug['session'] = $_SESSION;
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

$debug['user_id'] = getCurrentUserId();
$debug['user_name'] = $_SESSION['user_name'] ?? 'Unknown';
$debug['user_level'] = getCurrentUserLevel();
$debug['is_admin'] = isAdmin();

// Get JSON input
$input = file_get_contents('php://input');
$debug['raw_input'] = $input;

$data = json_decode($input, true);
$debug['parsed_data'] = $data;

if (!$data) {
    $debug['error'] = 'Invalid JSON';
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

// Validate required fields
if (empty($data['requisition_id']) || empty($data['approval_level']) || empty($data['action'])) {
    $debug['error'] = 'Missing required fields';
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

$requisition_id = (int)$data['requisition_id'];
$approval_level = (int)$data['approval_level'];
$action = $data['action'];
$remarks = $data['remarks'] ?? '';

$debug['requisition_id'] = $requisition_id;
$debug['approval_level'] = $approval_level;
$debug['action'] = $action;

// Connect to database
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    $debug['error'] = 'Database connection failed';
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

// Get requisition request
$stmt = $pdo->prepare("SELECT * FROM requisition_requests WHERE id = :id");
$stmt->execute(['id' => $requisition_id]);
$request = $stmt->fetch();

$debug['request_found'] = $request ? 'YES' : 'NO';
if ($request) {
    $debug['request_status'] = $request['status'];
    $debug['request_current_level'] = $request['current_approval_level'];
}

// Get approval record BEFORE update
$stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :req_id AND approval_level = :level");
$stmt->execute(['req_id' => $requisition_id, 'level' => $approval_level]);
$approval_before = $stmt->fetch();

$debug['approval_before'] = $approval_before;

// Try to update approval record
$stmt = $pdo->prepare("
    UPDATE approvals 
    SET status = :status,
        approver_name = :approver_name,
        remarks = :remarks,
        approved_at = NOW()
    WHERE requisition_id = :requisition_id 
    AND approval_level = :approval_level
");

$update_result = $stmt->execute([
    'status' => $action,
    'approver_name' => $debug['user_name'],
    'remarks' => $remarks,
    'requisition_id' => $requisition_id,
    'approval_level' => $approval_level
]);

$debug['update_executed'] = $update_result ? 'YES' : 'NO';
$debug['rows_affected'] = $stmt->rowCount();

// Get approval record AFTER update
$stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :req_id AND approval_level = :level");
$stmt->execute(['req_id' => $requisition_id, 'level' => $approval_level]);
$approval_after = $stmt->fetch();

$debug['approval_after'] = $approval_after;

// Check if update actually changed anything
$debug['approval_changed'] = ($approval_before['status'] !== $approval_after['status']) ? 'YES' : 'NO';

// Try to update requisition_requests table
if ($action === 'approved' && $approval_level < 5) {
    $next_level = $approval_level + 1;
    $stmt = $pdo->prepare("
        UPDATE requisition_requests 
        SET current_approval_level = :level,
            updated_at = NOW()
        WHERE id = :id
    ");
    $req_update = $stmt->execute([
        'level' => $next_level,
        'id' => $requisition_id
    ]);
    
    $debug['requisition_update'] = $req_update ? 'YES' : 'NO';
    $debug['requisition_rows_affected'] = $stmt->rowCount();
}

// Get final request state
$stmt = $pdo->prepare("SELECT * FROM requisition_requests WHERE id = :id");
$stmt->execute(['id' => $requisition_id]);
$request_after = $stmt->fetch();

$debug['request_after'] = [
    'status' => $request_after['status'],
    'current_approval_level' => $request_after['current_approval_level'],
    'updated_at' => $request_after['updated_at']
];

// Get all approvals for this request
$stmt = $pdo->prepare("SELECT approval_level, status, approver_name, approved_at FROM approvals WHERE requisition_id = :id ORDER BY approval_level");
$stmt->execute(['id' => $requisition_id]);
$all_approvals = $stmt->fetchAll();

$debug['all_approvals'] = $all_approvals;

echo json_encode($debug, JSON_PRETTY_PRINT);
