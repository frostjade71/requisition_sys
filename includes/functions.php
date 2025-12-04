<?php
/**
 * Core Utility Functions
 * Reusable functions for the application
 */

/**
 * Generate unique RF Control Number
 * Format: RF-YYYYMMDD-XXXX
 * @param PDO $pdo Database connection
 * @return string Generated RF number
 */
function generateRFNumber($pdo) {
    $date = date('Ymd');
    $prefix = "RF-{$date}-";
    
    // Get the last RF number for today
    $stmt = $pdo->prepare("
        SELECT rf_control_number 
        FROM requisition_requests 
        WHERE rf_control_number LIKE :prefix 
        ORDER BY rf_control_number DESC 
        LIMIT 1
    ");
    $stmt->execute(['prefix' => $prefix . '%']);
    $lastRF = $stmt->fetch();
    
    if ($lastRF) {
        // Extract the sequence number and increment
        $lastSequence = (int)substr($lastRF['rf_control_number'], -4);
        $newSequence = $lastSequence + 1;
    } else {
        // First request of the day
        $newSequence = 1;
    }
    
    // Format with leading zeros
    return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
}

/**
 * Sanitize user input
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Get status badge HTML
 * @param string $status Status value
 * @return string HTML badge
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'completed' => '<span class="badge badge-info">Completed</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirectTo($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool True if admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Get current user ID
 * @return int|null User ID or null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user approval level
 * @return int|null Approval level or null
 */
function getCurrentUserLevel() {
    return $_SESSION['approval_level'] ?? null;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send JSON response
 * @param bool $success Success status
 * @param string $message Message
 * @param array $data Additional data
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Log activity
 * @param PDO $pdo Database connection
 * @param string $action Action performed
 * @param string $details Action details
 */
function logActivity($pdo, $action, $details = '') {
    // This can be expanded to log to a database table
    error_log("[" . date('Y-m-d H:i:s') . "] {$action}: {$details}");
}
