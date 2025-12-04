<?php
/**
 * API Endpoint: Process Approval
 * Handles approve/reject actions from approvers
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

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required');
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log incoming data for debugging
    error_log('Process Approval - Incoming data: ' . print_r($data, true));
    
    if (!$data) {
        jsonResponse(false, 'Invalid JSON data');
    }
    
    // Validate required fields
    if (empty($data['requisition_id']) || empty($data['approval_level']) || empty($data['action'])) {
        jsonResponse(false, 'Missing required fields');
    }
    
    $requisition_id = (int)$data['requisition_id'];
    $approval_level = (int)$data['approval_level'];
    $action = $data['action']; // 'approved' or 'rejected'
    $remarks = sanitizeInput($data['remarks'] ?? '');
    
    // Validate action
    if (!in_array($action, ['approved', 'rejected'])) {
        jsonResponse(false, 'Invalid action');
    }
    
    // Get user info
    $user_id = getCurrentUserId();
    $user_name = $_SESSION['user_name'];
    $user_level = getCurrentUserLevel();
    $is_admin = isAdmin();
    
    // Connect to database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        jsonResponse(false, 'Database connection failed');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Get requisition request
        $stmt = $pdo->prepare("SELECT * FROM requisition_requests WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $requisition_id]);
        $request = $stmt->fetch();
        
        if (!$request) {
            throw new Exception('Request not found');
        }
        
        // Log request details for debugging
        error_log("Request Details - ID: {$request['id']}, Status: {$request['status']}, Current Level: {$request['current_approval_level']}");
        error_log("User Details - ID: {$user_id}, Name: {$user_name}, Level: {$user_level}, Is Admin: " . ($is_admin ? 'YES' : 'NO'));
        error_log("Form Data - Req ID: {$requisition_id}, Approval Level: {$approval_level}, Action: {$action}");
        
        // Verify request is pending
        if ($request['status'] != STATUS_PENDING) {
            error_log("ERROR: Request status is '{$request['status']}', not 'pending'");
            throw new Exception('Request is not pending');
        }
        
        // Verify approval level matches (unless admin)
        if (!$is_admin && $request['current_approval_level'] != $user_level) {
            error_log("ERROR: Level mismatch - User Level: {$user_level}, Request Current Level: {$request['current_approval_level']}");
            throw new Exception('You are not authorized to approve this level');
        }
        
        // Update approval record
        $stmt = $pdo->prepare("
            UPDATE approvals 
            SET status = :status,
                approver_name = :approver_name,
                remarks = :remarks,
                approved_at = NOW()
            WHERE requisition_id = :requisition_id 
            AND approval_level = :approval_level
        ");
        
        $stmt->execute([
            'status' => $action,
            'approver_name' => $user_name,
            'remarks' => $remarks,
            'requisition_id' => $requisition_id,
            'approval_level' => $approval_level
        ]);
        
        $rowsAffected = $stmt->rowCount();
        error_log("Approval update - Rows affected: {$rowsAffected} for requisition_id: {$requisition_id}, level: {$approval_level}");
        
        if ($rowsAffected === 0) {
            throw new Exception('No approval record found to update');
        }
        
        if ($action === 'rejected') {
            // Mark request as rejected
            $stmt = $pdo->prepare("
                UPDATE requisition_requests 
                SET status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'status' => STATUS_REJECTED,
                'id' => $requisition_id
            ]);
            
            $message = 'Request rejected successfully';
            
        } else {
            // Approved - check if this is the final level
            if ($approval_level == 5) {
                // Final approval - mark as approved
                $stmt = $pdo->prepare("
                    UPDATE requisition_requests 
                    SET status = :status,
                        current_approval_level = :level,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    'status' => STATUS_APPROVED,
                    'level' => 5,
                    'id' => $requisition_id
                ]);
                
                $message = 'Request approved successfully (Final Approval)';
                
            } else {
                // Move to next level
                $next_level = $approval_level + 1;
                
                $stmt = $pdo->prepare("
                    UPDATE requisition_requests 
                    SET current_approval_level = :level,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    'level' => $next_level,
                    'id' => $requisition_id
                ]);
                
                $message = "Request approved successfully. Moved to Level {$next_level}";
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Log activity
        logActivity($pdo, 'APPROVAL_ACTION', "RF: {$request['rf_control_number']} - {$action} by {$user_name} at Level {$approval_level}");
        
        // Return success response
        jsonResponse(true, $message, [
            'requisition_id' => $requisition_id,
            'action' => $action,
            'new_status' => $action === 'rejected' ? STATUS_REJECTED : ($approval_level == 5 ? STATUS_APPROVED : STATUS_PENDING)
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Process Approval Error: ' . $e->getMessage());
    jsonResponse(false, $e->getMessage());
}
