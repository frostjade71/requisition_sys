<?php
/**
 * Debug API Endpoint
 * Test session and authentication
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

$debug_info = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'is_logged_in' => isLoggedIn(),
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'user_name' => $_SESSION['user_name'] ?? 'NOT SET',
    'approval_level' => $_SESSION['approval_level'] ?? 'NOT SET',
    'is_admin' => $_SESSION['is_admin'] ?? 'NOT SET',
    'all_session_data' => $_SESSION
];

echo json_encode($debug_info, JSON_PRETTY_PRINT);
