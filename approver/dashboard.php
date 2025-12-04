<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAuth();

$page_title = 'Approver Dashboard - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

$user_level = getCurrentUserLevel();
$user_name = $_SESSION['user_name'];

// Get pending requests at user's level
$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM requisition_items WHERE requisition_id = r.id) as item_count
    FROM requisition_requests r
    WHERE r.current_approval_level = :level
    AND r.status = 'pending'
    ORDER BY r.created_at DESC
");
$stmt->execute(['level' => $user_level]);
$pending_requests = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_pending
    FROM requisition_requests
    WHERE current_approval_level = :level
    AND status = 'pending'
");
$stmt->execute(['level' => $user_level]);
$stats = $stmt->fetch();

// Get approved today count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as approved_today
    FROM approvals
    WHERE approval_level = :level
    AND status = 'approved'
    AND DATE(approved_at) = CURDATE()
    AND approver_name = :name
");
$stmt->execute([
    'level' => $user_level,
    'name' => $user_name
]);
$approved_today = $stmt->fetch()['approved_today'];
?>

<div class="container">
    <!-- Welcome Header -->
    <div class="dashboard-header">
        <div>
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p class="subtitle"><?php echo $_SESSION['user_role']; ?></p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="background-color: rgba(241, 196, 15, 0.1);">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/pending.png" alt="Pending" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['total_pending']; ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="background-color: rgba(46, 204, 113, 0.1);">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/check.png" alt="Approved" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $approved_today; ?></div>
                <div class="stat-label">Approved Today</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="background-color: rgba(155, 89, 182, 0.1);">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/calendar.png" alt="Calendar" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value" id="currentDate"><?php echo date('M d, Y'); ?></div>
                <div class="stat-label" id="currentTime" style="font-size: 1rem; margin-top: 4px;"><?php echo date('h:i:s A'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Pending Requests -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/history.png" alt="" style="width: 28px; height: 28px; vertical-align: middle; margin-right: 8px;">
                Pending Requests
            </h2>
        </div>
        
        <?php if (empty($pending_requests)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Pending Requests</h3>
                <p>There are currently no requisition requests pending at your approval level.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>RF Number</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_requests as $request): ?>
                            <tr>
                                <td>
                                    <span class="rf-number-badge">
                                        <?php echo htmlspecialchars($request['rf_control_number']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['department']); ?></td>
                                <td><?php echo $request['item_count']; ?> item(s)</td>
                                <td><?php echo formatDate($request['created_at'], 'M d, Y'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/approver/view_request.php?id=<?php echo $request['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
    }
    
    .dashboard-header h1 {
        color: var(--dark);
        margin-bottom: var(--spacing-xs);
    }
    
    .subtitle {
        color: var(--primary-color);
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }
    
    .stat-card {
        background-color: var(--white);
        padding: var(--spacing-lg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        display: flex;
        gap: var(--spacing-lg);
        align-items: center;
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    
    .stat-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }
    
    .stat-details {
        flex: 1;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--dark);
        line-height: 1.2;
        margin-bottom: var(--spacing-xs);
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--gray);
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .rf-number-badge {
        font-family: 'Courier New', monospace;
        background-color: var(--lighter-gray);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .empty-state {
        text-align: center;
        padding: var(--spacing-xxl);
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: var(--spacing-md);
    }
    
    .empty-state h3 {
        color: var(--dark-gray);
        margin-bottom: var(--spacing-sm);
    }
    
    .empty-state p {
        color: var(--gray);
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-md);
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Update clock every second
    function updateClock() {
        const now = new Date();
        
        // Format time as HH:MM:SS AM/PM
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        const timeString = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
        
        // Update the time display
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }
    
    // Update immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
