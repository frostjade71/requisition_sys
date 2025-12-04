<?php
/**
 * Authentication Middleware
 * Protects pages that require login
 */

// Load configuration first (before session starts)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session after configuration is loaded
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require authentication
 * Redirects to login if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        redirectTo(BASE_URL . '/approver/login.php');
    }
}

/**
 * Require admin access
 * Redirects to approver dashboard if not admin
 */
function requireAdmin() {
    requireAuth();
    
    if (!isAdmin()) {
        redirectTo(BASE_URL . '/approver/dashboard.php');
    }
}

/**
 * Require specific approval level
 * @param int $level Required approval level
 */
function requireLevel($level) {
    requireAuth();
    
    $userLevel = getCurrentUserLevel();
    
    if ($userLevel != $level && !isAdmin()) {
        redirectTo(BASE_URL . '/approver/dashboard.php');
    }
}
