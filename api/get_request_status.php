<?php
/**
 * API Endpoint: Get Request Status
 * Retrieves requisition request details, items, and approval timeline
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method');
}

try {
    // Get RF number from query parameter
    $rf_number = $_GET['rf_number'] ?? '';
    
    if (empty($rf_number)) {
        jsonResponse(false, 'RF Control Number is required');
    }
    
    $rf_number = sanitizeInput($rf_number);
    
    // Validate RF number format
    if (!preg_match('/^RF-\d{8}-\d{4}$/', $rf_number)) {
        jsonResponse(false, 'Invalid RF Control Number format');
    }
    
    // Connect to database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        jsonResponse(false, 'Database connection failed');
    }
    
    // Get requisition request
    $stmt = $pdo->prepare("
        SELECT * FROM requisition_requests 
        WHERE rf_control_number = :rf_number
    ");
    $stmt->execute(['rf_number' => $rf_number]);
    $request = $stmt->fetch();
    
    if (!$request) {
        jsonResponse(false, 'Request not found');
    }
    
    // Get requisition items
    $stmt = $pdo->prepare("
        SELECT * FROM requisition_items 
        WHERE requisition_id = :requisition_id 
        ORDER BY id ASC
    ");
    $stmt->execute(['requisition_id' => $request['id']]);
    $items = $stmt->fetchAll();
    
    // Get approval records
    $stmt = $pdo->prepare("
        SELECT * FROM approvals 
        WHERE requisition_id = :requisition_id 
        ORDER BY approval_level ASC
    ");
    $stmt->execute(['requisition_id' => $request['id']]);
    $approvals = $stmt->fetchAll();
    
    // Return data
    jsonResponse(true, 'Request found', [
        'request' => $request,
        'items' => $items,
        'approvals' => $approvals
    ]);
    
} catch (Exception $e) {
    error_log('Get Request Status Error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred while retrieving request status');
}
