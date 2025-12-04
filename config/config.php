<?php
/**
 * Application Configuration
 * Global settings and constants
 */

// Application Settings
define('APP_NAME', 'LEYECO III Requisition System');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('BASE_URL', getenv('APP_URL') ?: 'http://localhost:8080');

// Timezone
date_default_timezone_set('Asia/Manila');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Departments
define('DEPARTMENTS', [
    'Engineering Department',
    'Operations Department',
    'Technical Services',
    'Administrative Department',
    'Finance Department',
    'Human Resources',
    'Customer Service',
    'Maintenance Department'
]);

// Units of Measurement
define('UNITS', [
    'pcs',
    'kg',
    'meters',
    'liters',
    'boxes',
    'rolls',
    'sets',
    'pairs',
    'units'
]);

// Request Status
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');
define('STATUS_COMPLETED', 'completed');

// Approval Levels
define('APPROVAL_LEVELS', [
    1 => 'Recommending Approval - Section Head/Div. Head/Department Head',
    2 => 'Inventory Checked - Warehouse Section Head',
    3 => 'Budget Approval - Div. Supervisor/Budget Officer',
    4 => 'Checked By - Internal Auditor',
    5 => 'Approved By - General Manager'
]);

// Pagination
define('ITEMS_PER_PAGE', 20);
