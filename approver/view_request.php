<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAuth();

$page_title = 'View Request - LEYECO III Requisition System';
require_once __DIR__ . '/../config/database.php';

// Get request ID
$request_id = $_GET['id'] ?? 0;

if (!$request_id) {
    header('Location: ' . BASE_URL . '/approver/dashboard.php');
    exit();
}

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Get request details
$stmt = $pdo->prepare("SELECT * FROM requisition_requests WHERE id = :id");
$stmt->execute(['id' => $request_id]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: ' . BASE_URL . '/approver/dashboard.php');
    exit();
}

// Get items
$stmt = $pdo->prepare("SELECT * FROM requisition_items WHERE requisition_id = :id");
$stmt->execute(['id' => $request_id]);
$items = $stmt->fetchAll();

// Get approvals
$stmt = $pdo->prepare("SELECT * FROM approvals WHERE requisition_id = :id ORDER BY approval_level ASC");
$stmt->execute(['id' => $request_id]);
$approvals = $stmt->fetchAll();

$user_level = getCurrentUserLevel();
$can_approve = ($request['current_approval_level'] == $user_level && $request['status'] == 'pending') || isAdmin();
// Level 2 (Warehouse Section Head) can edit warehouse_inventory only
// Level 3 (Budget Officer) can edit balance_for_purchase only
// Level 4 (Internal Auditor) can edit remarks only
$can_edit_warehouse = ($user_level == 2) || isAdmin();
$can_edit_balance = ($user_level == 3) || isAdmin();
$can_edit_remarks = ($user_level == 4) || isAdmin();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Requisition Request Details</h1>
        <a href="<?php echo BASE_URL; ?><?php echo isAdmin() ? '/admin/dashboard.php' : '/approver/dashboard.php'; ?>" class="btn btn-outline">← Back to Dashboard</a>
    </div>
    
    <!-- Request Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Request Information</h3>
        </div>
        
        <div class="request-details">
            <div class="detail-item">
                <div class="detail-label">RF Control Number</div>
                <div class="detail-value rf-number"><?php echo htmlspecialchars($request['rf_control_number']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Requester Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['requester_name']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Department</div>
                <div class="detail-value"><?php echo htmlspecialchars($request['department']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value"><?php echo getStatusBadge($request['status']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Current Level</div>
                <div class="detail-value">Level <?php echo $request['current_approval_level']; ?> of 5</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Submitted Date</div>
                <div class="detail-value"><?php echo formatDate($request['created_at']); ?></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">Purpose</div>
                <div class="detail-value" style="font-weight: normal;"><?php echo nl2br(htmlspecialchars($request['purpose'])); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Items -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Requisition Items</h3>
            <?php if ($can_edit_warehouse && !$can_edit_balance && !$can_edit_remarks): ?>
                <span class="edit-hint">💡 Edit Warehouse Stock before Approving</span>
            <?php elseif ($can_edit_balance && !$can_edit_warehouse && !$can_edit_remarks): ?>
                <span class="edit-hint">💡 Edit Balance to Purchase before Approving</span>
            <?php elseif ($can_edit_remarks && !$can_edit_warehouse && !$can_edit_balance): ?>
                <span class="edit-hint">💡 Edit Remarks Status before Approving</span>
            <?php elseif ($can_edit_warehouse && $can_edit_balance && $can_edit_remarks): ?>
                <span class="edit-hint">💡 All fields are editable</span>
            <?php endif; ?>
        </div>
        
        <div class="table-responsive">
            <table id="itemsTable">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Description</th>
                        <th>Warehouse Stock</th>
                        <th>Balance to Purchase</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr data-item-id="<?php echo $item['id']; ?>">
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($item['unit']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td class="<?php echo $can_edit_warehouse ? 'editable-cell' : ''; ?>" 
                                data-field="warehouse_inventory" 
                                data-value="<?php echo $item['warehouse_inventory'] ?: 0; ?>">
                                <?php echo $item['warehouse_inventory'] ?: 'N/A'; ?>
                                <?php if ($can_edit_warehouse): ?>
                                    <span class="edit-icon">✏️</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo $can_edit_balance ? 'editable-cell' : ''; ?>" 
                                data-field="balance_for_purchase" 
                                data-value="<?php echo $item['balance_for_purchase'] ?: 0; ?>">
                                <?php 
                                    if ($item['balance_for_purchase']) {
                                        // Check if value is numeric
                                        if (is_numeric($item['balance_for_purchase'])) {
                                            echo '₱ ' . htmlspecialchars($item['balance_for_purchase']);
                                        } else {
                                            echo htmlspecialchars($item['balance_for_purchase']);
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                ?>
                                <?php if ($can_edit_balance): ?>
                                    <span class="edit-icon">✏️</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo $can_edit_remarks ? 'editable-cell remarks-cell' : ''; ?>" 
                                data-field="remarks" 
                                data-value="<?php echo htmlspecialchars($item['remarks']) ?: ''; ?>">
                                <?php echo htmlspecialchars($item['remarks']) ?: '-'; ?>
                                <?php if ($can_edit_remarks): ?>
                                    <span class="edit-icon">✏️</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Approval Actions -->
    <?php if ($can_approve): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Approval Decision</h3>
            </div>
            
            <form id="approvalForm">
                <input type="hidden" name="requisition_id" value="<?php echo $request['id']; ?>">
                <input type="hidden" name="approval_level" value="<?php echo $request['current_approval_level']; ?>">
                
                <div class="form-group">
                    <label for="remarks">Comments (Optional)</label>
                    <textarea id="remarks" name="remarks" rows="4" placeholder="Enter your comment"></textarea>
                </div>
                
                <div class="approval-actions">
                    <button type="button" class="btn btn-danger" onclick="handleApproval('rejected')">
                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/decline.png" alt="Reject" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: text-bottom;"> Reject Request
                    </button>
                    <button type="button" class="btn btn-success btn-lg" onclick="handleApproval('approved')">
                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/approve.png" alt="Approve" style="width: 20px; height: 20px; margin-right: 6px; vertical-align: text-bottom;"> Approve Request
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- Approval Timeline -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Approval Timeline</h3>
        </div>
        
        <div class="timeline compact">
            <?php foreach ($approvals as $approval): ?>
                <div class="timeline-item <?php echo $approval['status']; ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div>
                                <div class="timeline-level">Level <?php echo $approval['approval_level']; ?></div>
                                <div class="timeline-title"><?php echo htmlspecialchars($approval['approver_role']); ?></div>
                            </div>
                            <div><?php echo getStatusBadge($approval['status']); ?></div>
                        </div>
                        
                        <?php if ($approval['status'] != 'pending'): ?>
                            <div class="timeline-meta">
                                <div><strong>Approver:</strong> <?php echo htmlspecialchars($approval['approver_name']); ?></div>
                                <div><strong>Date:</strong> <?php echo formatDate($approval['approved_at']); ?></div>
                                <?php if ($approval['remarks']): ?>
                                    <div class="timeline-remarks">
                                        <strong>Comment:</strong> <?php echo nl2br(htmlspecialchars($approval['remarks'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    /* Compact Action Required Section */
    #approvalForm {
        max-width: 600px;
        margin: 0 auto;
    }
    
    #approvalForm .card-header {
        padding: 12px 20px;
    }
    
    #approvalForm .card-header .card-title {
        font-size: 1rem;
        margin: 0;
    }
    
    #approvalForm .form-group {
        margin-bottom: 12px;
    }
    
    #approvalForm .form-group label {
        font-size: 0.875rem;
        margin-bottom: 6px;
    }
    
    #approvalForm textarea {
        font-size: 0.875rem;
        padding: 10px;
        min-height: 60px;
    }
    
    #approvalForm .approval-actions {
        margin-top: 12px;
        gap: 10px;
        justify-content: center;
    }
    
    #approvalForm .approval-actions button {
        padding: 8px 16px;
        font-size: 0.875rem;
    }
    
    #approvalForm .approval-actions button img {
        width: 14px !important;
        height: 14px !important;
        margin-right: 4px !important;
    }
    
    #approvalForm .approval-actions .btn-lg {
        padding: 10px 20px;
        font-size: 0.9375rem;
    }
    
    #approvalForm .approval-actions .btn-lg img {
        width: 16px !important;
        height: 16px !important;
    }
    
    /* Approval Action Buttons */
    .approval-actions button {
        transition: all 0.2s ease-in-out;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .approval-actions button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .approval-actions button:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
    
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
    }
    
    .request-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .detail-item {
        padding: var(--spacing-md);
        background-color: var(--lighter-gray);
        border-radius: var(--radius-md);
    }
    
    .detail-label {
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: var(--spacing-xs);
        font-weight: 500;
    }
    
    .detail-value {
        font-size: 1.125rem;
        color: var(--dark);
        font-weight: 600;
    }
    
    .detail-value.rf-number {
        font-family: 'Courier New', monospace;
        color: var(--primary-color);
    }
    
    .approval-actions {
        display: flex;
        gap: var(--spacing-md);
        justify-content: flex-end;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-md);
        }
        
        .approval-actions {
            flex-direction: column-reverse;
        }
        
        .approval-actions button {
            width: 100%;
        }
        
        #approvalForm {
            max-width: 100%;
        }
    }
    
    /* Editable Inventory Fields */
    .edit-hint {
        font-size: 0.875rem;
        color: var(--primary-color);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .editable-cell {
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        background-color: #fffbf0;
        font-weight: 600;
    }
    
    .editable-cell:hover {
        background-color: #fff4d6;
        box-shadow: inset 0 0 0 2px var(--primary-color);
    }
    
    .editable-cell .edit-icon {
        opacity: 0;
        transition: opacity 0.2s ease;
        margin-left: 6px;
        font-size: 0.875rem;
    }
    
    .editable-cell:hover .edit-icon {
        opacity: 1;
    }
    
    .editable-cell.editing {
        background-color: #fff;
        padding: 0;
    }
    
    .editable-cell input {
        width: 100%;
        padding: 12px;
        border: 2px solid var(--primary-color);
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        outline: none;
    }
    
    .editable-cell select {
        width: 100%;
        padding: 12px;
        border: 2px solid var(--primary-color);
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        outline: none;
        background-color: #fff;
        cursor: pointer;
    }
    
    .editable-cell input:focus,
    .editable-cell select:focus {
        border-color: var(--success-color);
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

</style>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/tracking.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/timeline-compact.css">

<script>
async function handleApproval(action) {
    const form = document.getElementById('approvalForm');
    const formData = new FormData(form);
    
    const data = {
        requisition_id: formData.get('requisition_id'),
        approval_level: formData.get('approval_level'),
        action: action,
        remarks: formData.get('remarks')
    };
    
    console.log('=== APPROVAL DEBUG ===');
    console.log('Action:', action);
    console.log('Data being sent:', data);
    console.log('Requisition ID:', data.requisition_id, 'Type:', typeof data.requisition_id);
    console.log('Approval Level:', data.approval_level, 'Type:', typeof data.approval_level);
    
    const confirmMsg = action === 'approved' 
        ? 'Are you sure you want to approve this request?' 
        : 'Are you sure you want to reject this request?';
    
    // Use custom confirm modal with callbacks
    confirm(confirmMsg, async function() {
        // User clicked "Confirm"
        console.log('User confirmed, proceeding with approval...');
        
        Loading.show();
        
        try {
            console.log('Sending request to:', '<?php echo BASE_URL; ?>/api/process_approval.php');
            const response = await Ajax.post('<?php echo BASE_URL; ?>/api/process_approval.php', data);
            
            console.log('Response received:', response);
            console.log('Success:', response.success);
            console.log('Message:', response.message);
            
            Loading.hide();
            
            if (response.success) {
                Toast.show(response.message, 'success');
                console.log('Approval successful, reloading in 1.5s...');
                setTimeout(() => {
                    console.log('Reloading page now...');
                    // Reload the current page to show updated timeline
                    window.location.reload();
                }, 1500);
            } else {
                console.error('Approval failed:', response.message);
                console.error('Full response:', response);
                Toast.show(response.message || 'Action failed', 'error');
            }
        } catch (error) {
            Loading.hide();
            console.error('Approval error:', error);
            console.error('Error stack:', error.stack);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            Toast.show('An error occurred. Please try again.', 'error');
        }
    }, function() {
        // User clicked "Cancel"
        console.log('User cancelled');
    });
}

<?php if ($can_edit_warehouse || $can_edit_balance || $can_edit_remarks): ?>
// Inline Editing for Inventory Fields
document.addEventListener('DOMContentLoaded', function() {
    const editableCells = document.querySelectorAll('.editable-cell');
    
    editableCells.forEach(cell => {
        cell.addEventListener('click', function() {
            // Don't edit if already editing
            if (this.classList.contains('editing')) return;
            
            const currentValue = this.dataset.value || '';
            const field = this.dataset.field;
            const itemId = this.closest('tr').dataset.itemId;
            
            // Store original content
            const originalContent = this.innerHTML;
            
            // Check if this is the remarks field (for dropdown)
            if (field === 'remarks') {
                // Create dropdown for remarks
                const select = document.createElement('select');
                select.className = 'inventory-input';
                let saved = false;
                
                // Add options
                const options = ['', 'Reviewing', 'Incomplete', 'Verified'];
                options.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt;
                    option.textContent = opt || '-';
                    if (opt === currentValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
                
                // Replace content with select
                this.innerHTML = '';
                this.appendChild(select);
                this.classList.add('editing');
                
                // Focus
                select.focus();
                
                // Save on change
                select.addEventListener('change', async function() {
                    if (!saved) {
                        saved = true;
                        await saveInventoryValue(cell, itemId, field, select.value, originalContent);
                    }
                });
                
                // Save on blur (only if not already saved)
                select.addEventListener('blur', async function() {
                    if (!saved) {
                        saved = true;
                        await saveInventoryValue(cell, itemId, field, select.value, originalContent);
                    }
                });
                
                // Cancel on Escape
                select.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        cell.innerHTML = originalContent;
                        cell.classList.remove('editing');
                    }
                });
            } else {
                // Create input for other fields
                const input = document.createElement('input');
                input.type = 'text';
                input.value = currentValue;
                input.className = 'inventory-input';
                input.placeholder = 'Enter value...';
                
                // Replace content with input
                this.innerHTML = '';
                this.appendChild(input);
                this.classList.add('editing');
                
                // Focus and select
                input.focus();
                input.select();
                
                // Save on blur
                input.addEventListener('blur', async function() {
                    await saveInventoryValue(cell, itemId, field, input.value, originalContent);
                });
                
                // Save on Enter, cancel on Escape
                input.addEventListener('keydown', async function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        await saveInventoryValue(cell, itemId, field, input.value, originalContent);
                    } else if (e.key === 'Escape') {
                        cell.innerHTML = originalContent;
                        cell.classList.remove('editing');
                    }
                });
            }
        });
    });
});

async function saveInventoryValue(cell, itemId, field, newValue, originalContent) {
    const oldValue = cell.dataset.value || '';
    
    // Trim whitespace
    newValue = newValue.trim();
    
    // If value hasn't changed, just restore
    if (newValue === oldValue) {
        cell.innerHTML = originalContent;
        cell.classList.remove('editing');
        return;
    }
    
    // Only validate that it's not empty (except for remarks which can be empty)
    if (newValue === '' && field !== 'remarks') {
        Toast.show('Please enter a value', 'error');
        cell.innerHTML = originalContent;
        cell.classList.remove('editing');
        return;
    }
    
    Loading.show();
    
    try {
        const response = await Ajax.post('<?php echo BASE_URL; ?>/api/update_inventory.php', {
            item_id: itemId,
            field: field,
            value: newValue
        });
        
        Loading.hide();
        
        if (response.success) {
            // Update the cell
            cell.dataset.value = newValue;
            // Display the value with currency symbol for balance_for_purchase if numeric
            if (field === 'balance_for_purchase') {
                // Check if value is numeric (including numbers with commas like 7,100)
                const cleanedValue = newValue.replace(/,/g, '');
                if (!isNaN(cleanedValue) && cleanedValue.trim() !== '') {
                    cell.innerHTML = `₱ ${newValue} <span class="edit-icon">✏️</span>`;
                } else {
                    // Text value, no currency symbol
                    cell.innerHTML = `${newValue} <span class="edit-icon">✏️</span>`;
                }
            } else if (field === 'remarks') {
                // For remarks, show '-' if empty
                const displayValue = newValue || '-';
                cell.innerHTML = `${displayValue} <span class="edit-icon">✏️</span>`;
            } else {
                cell.innerHTML = `${newValue} <span class="edit-icon">✏️</span>`;
            }
            cell.classList.remove('editing');
            
            Toast.show(response.message || 'Updated successfully', 'success');
        } else {
            Toast.show(response.message || 'Update failed', 'error');
            cell.innerHTML = originalContent;
            cell.classList.remove('editing');
        }
    } catch (error) {
        Loading.hide();
        console.error('Update error:', error);
        Toast.show('An error occurred while updating', 'error');
        cell.innerHTML = originalContent;
        cell.classList.remove('editing');
    }
}
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
