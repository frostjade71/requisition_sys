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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Requisition Request Details</h1>
        <a href="<?php echo BASE_URL; ?>/approver/dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
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
        </div>
        
        <div class="table-responsive">
            <table>
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
                        <tr>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($item['unit']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td><?php echo $item['warehouse_inventory'] ?: 'N/A'; ?></td>
                            <td><?php echo $item['balance_for_purchase'] ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($item['remarks']) ?: '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
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
                                        <strong>Remarks:</strong> <?php echo nl2br(htmlspecialchars($approval['remarks'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Approval Actions -->
    <?php if ($can_approve): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your Action Required</h3>
            </div>
            
            <form id="approvalForm">
                <input type="hidden" name="requisition_id" value="<?php echo $request['id']; ?>">
                <input type="hidden" name="approval_level" value="<?php echo $request['current_approval_level']; ?>">
                
                <div class="form-group">
                    <label for="remarks">Remarks (Optional)</label>
                    <textarea id="remarks" name="remarks" rows="4" placeholder="Enter your remarks or comments..."></textarea>
                </div>
                
                <div class="approval-actions">
                    <button type="button" class="btn btn-danger" onclick="handleApproval('rejected')">
                        ✗ Reject Request
                    </button>
                    <button type="button" class="btn btn-success btn-lg" onclick="handleApproval('approved')">
                        ✓ Approve Request
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
