<?php
/**
 * API Endpoint: Submit Requisition Request
 * Handles form submission and creates new requisition with items
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        jsonResponse(false, 'Invalid JSON data');
    }
    
    // Validate required fields
    if (empty($data['requester_name']) || empty($data['department']) || empty($data['purpose'])) {
        jsonResponse(false, 'Please fill in all required fields');
    }
    
    if (empty($data['items']) || !is_array($data['items'])) {
        jsonResponse(false, 'At least one item is required');
    }
    
    // Sanitize inputs
    $requester_name = sanitizeInput($data['requester_name']);
    $department = sanitizeInput($data['department']);
    $purpose = sanitizeInput($data['purpose']);
    
    // Validate department
    if (!in_array($department, DEPARTMENTS)) {
        jsonResponse(false, 'Invalid department selected');
    }
    
    // Connect to database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        jsonResponse(false, 'Database connection failed');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Generate RF Control Number
        $rf_control_number = generateRFNumber($pdo);
        
        // Insert requisition request
        $stmt = $pdo->prepare("
            INSERT INTO requisition_requests 
            (rf_control_number, requester_name, department, purpose, status, current_approval_level) 
            VALUES 
            (:rf_number, :requester_name, :department, :purpose, :status, :level)
        ");
        
        $stmt->execute([
            'rf_number' => $rf_control_number,
            'requester_name' => $requester_name,
            'department' => $department,
            'purpose' => $purpose,
            'status' => STATUS_PENDING,
            'level' => 1
        ]);
        
        $requisition_id = $pdo->lastInsertId();
        
        // Insert requisition items
        $itemStmt = $pdo->prepare("
            INSERT INTO requisition_items 
            (requisition_id, quantity, unit, description, remarks) 
            VALUES 
            (:requisition_id, :quantity, :unit, :description, :remarks)
        ");
        
        foreach ($data['items'] as $item) {
            // Validate item data
            if (empty($item['quantity']) || empty($item['unit']) || empty($item['description'])) {
                throw new Exception('Invalid item data');
            }
            
            // Validate unit
            if (!in_array($item['unit'], UNITS)) {
                throw new Exception('Invalid unit: ' . $item['unit']);
            }
            
            $itemStmt->execute([
                'requisition_id' => $requisition_id,
                'quantity' => (int)$item['quantity'],
                'unit' => sanitizeInput($item['unit']),
                'description' => sanitizeInput($item['description']),
                'remarks' => sanitizeInput($item['remarks'] ?? '')
            ]);
        }
        
        // Create approval records for all 5 levels
        $approvalStmt = $pdo->prepare("
            INSERT INTO approvals 
            (requisition_id, approval_level, approver_role, status) 
            VALUES 
            (:requisition_id, :level, :role, :status)
        ");
        
        foreach (APPROVAL_LEVELS as $level => $role) {
            $approvalStmt->execute([
                'requisition_id' => $requisition_id,
                'level' => $level,
                'role' => $role,
                'status' => STATUS_PENDING
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Log activity
        logActivity($pdo, 'REQUEST_SUBMITTED', "RF: {$rf_control_number} by {$requester_name}");
        
        // Return success response
        jsonResponse(true, 'Request submitted successfully', [
            'rf_control_number' => $rf_control_number,
            'requisition_id' => $requisition_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Submit Request Error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred while processing your request: ' . $e->getMessage());
}
