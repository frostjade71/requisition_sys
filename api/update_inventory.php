<?php
/**
 * Update Inventory API
 * Allows Warehouse Section Head (Level 2), Budget Officer (Level 3), and Internal Auditor (Level 4) to update inventory fields
 */

require_once __DIR__ . '/../middleware/auth.php';
requireAuth();

header('Content-Type: application/json');

// Get user level
$user_level = getCurrentUserLevel();
$is_admin = isAdmin();

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['item_id']) || !isset($data['field']) || !isset($data['value'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$item_id = (int)$data['item_id'];
$field = $data['field'];
$value = trim($data['value']); // Accept text, just trim whitespace

// Validate field name (prevent SQL injection)
$allowed_fields = ['warehouse_inventory', 'balance_for_purchase', 'remarks'];
if (!in_array($field, $allowed_fields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid field name'
    ]);
    exit;
}

// Role-specific permission checks
if (!$is_admin) {
    // Level 2 (Warehouse Section Head) can ONLY edit warehouse_inventory
    if ($user_level == 2 && $field !== 'warehouse_inventory') {
        echo json_encode([
            'success' => false,
            'message' => 'Warehouse Section Head can only edit Warehouse Stock'
        ]);
        exit;
    }
    
    // Level 3 (Budget Officer) can ONLY edit balance_for_purchase
    if ($user_level == 3 && $field !== 'balance_for_purchase') {
        echo json_encode([
            'success' => false,
            'message' => 'Budget Officer can only edit Balance to Purchase'
        ]);
        exit;
    }
    
    // Level 4 (Internal Auditor) can ONLY edit remarks
    if ($user_level == 4 && $field !== 'remarks') {
        echo json_encode([
            'success' => false,
            'message' => 'Internal Auditor can only edit Remarks'
        ]);
        exit;
    }
    
    // Other levels cannot edit at all
    if ($user_level != 2 && $user_level != 3 && $user_level != 4) {
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to update inventory fields'
        ]);
        exit;
    }
}

// Validate value is not empty (except for remarks which can be empty)
if ($value === '' && $field !== 'remarks') {
    echo json_encode([
        'success' => false,
        'message' => 'Value cannot be empty'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Verify item exists
    $stmt = $pdo->prepare("SELECT id FROM requisition_items WHERE id = :id");
    $stmt->execute(['id' => $item_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
        exit;
    }
    
    // Update the field
    $sql = "UPDATE requisition_items SET {$field} = :value WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'value' => $value,
        'id' => $item_id
    ]);
    
    // Get field display name
    $field_names = [
        'warehouse_inventory' => 'Warehouse Stock',
        'balance_for_purchase' => 'Balance to Purchase',
        'remarks' => 'Remarks'
    ];
    $field_name = $field_names[$field] ?? $field;
    
    // Format the success message
    $display_value = $value !== '' ? $value : 'cleared';
    $message = "{$field_name} updated successfully";
    if ($value !== '') {
        $message = "{$field_name} updated to {$value}";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'item_id' => $item_id,
            'field' => $field,
            'value' => $value
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Inventory update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
