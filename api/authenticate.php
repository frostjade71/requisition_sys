<?php
/**
 * API Endpoint: Authenticate User
 * Handles approver login
 */

header('Content-Type: application/json');

// Load configuration first (before session starts)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session after configuration is loaded
session_start();

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
    if (empty($data['email']) || empty($data['password'])) {
        jsonResponse(false, 'Email and password are required');
    }
    
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    
    // Validate email format
    if (!validateEmail($email)) {
        jsonResponse(false, 'Invalid email format');
    }
    
    // Connect to database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        jsonResponse(false, 'Database connection failed');
    }
    
    // Get approver by email
    $stmt = $pdo->prepare("
        SELECT * FROM approvers 
        WHERE email = :email
    ");
    $stmt->execute(['email' => $email]);
    $approver = $stmt->fetch();
    
    if (!$approver) {
        // Log failed attempt
        logActivity($pdo, 'LOGIN_FAILED', "Failed login attempt for email: {$email}");
        jsonResponse(false, 'Invalid email or password');
    }
    
    // Verify password
    if (!password_verify($password, $approver['password'])) {
        // Log failed attempt
        logActivity($pdo, 'LOGIN_FAILED', "Failed login attempt for email: {$email}");
        jsonResponse(false, 'Invalid email or password');
    }
    
    // Create session
    $_SESSION['user_id'] = $approver['id'];
    $_SESSION['user_name'] = $approver['name'];
    $_SESSION['user_email'] = $approver['email'];
    $_SESSION['user_role'] = $approver['role'];
    $_SESSION['approval_level'] = $approver['approval_level'];
    $_SESSION['is_admin'] = (bool)$approver['is_admin'];
    $_SESSION['logged_in_at'] = time();
    
    // Handle remember me
    if (isset($data['remember_me']) && $data['remember_me']) {
        // Set cookie for 30 days
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        
        // Store token in database (you would need a remember_tokens table)
        // For simplicity, we're just setting the cookie
    }
    
    // Log successful login
    logActivity($pdo, 'LOGIN_SUCCESS', "User {$approver['name']} logged in");
    
    // Determine redirect URL
    $redirect_url = $approver['is_admin'] 
        ? BASE_URL . '/admin/dashboard.php' 
        : BASE_URL . '/approver/dashboard.php';
    
    // Return success response
    jsonResponse(true, 'Login successful', [
        'user' => [
            'id' => $approver['id'],
            'name' => $approver['name'],
            'email' => $approver['email'],
            'role' => $approver['role'],
            'approval_level' => $approver['approval_level'],
            'is_admin' => (bool)$approver['is_admin']
        ],
        'redirect_url' => $redirect_url
    ]);
    
} catch (Exception $e) {
    error_log('Authentication Error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred during authentication');
}
