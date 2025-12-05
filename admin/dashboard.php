<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAdmin();

$page_title = 'Admin Dashboard - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM requisition_requests");
$total_requests = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM requisition_requests WHERE status = 'pending'");
$pending_requests = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM requisition_requests WHERE status = 'approved'");
$approved_requests = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM requisition_requests WHERE status = 'rejected'");
$rejected_requests = $stmt->fetch()['total'];

// Get recent requests
$stmt = $pdo->query("
    SELECT r.*, 
           (SELECT COUNT(*) FROM requisition_items WHERE requisition_id = r.id) as item_count
    FROM requisition_requests r
    ORDER BY r.created_at DESC
    LIMIT 10
");
$recent_requests = $stmt->fetchAll();
?>

<div class="dashboard-container">
    <!-- Welcome Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/layout.png" alt="Dashboard" style="width: 36px; height: 36px; object-fit: contain; vertical-align: middle; margin-right: 12px;">
                Admin Dashboard
            </h1>
            <p class="subtitle">Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>. Here's what's happening today.</p>
        </div>
        <div class="stat-card datetime">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/calendar.png" alt="Date & Time" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value" id="currentDate"><?php echo date('M d, Y'); ?></div>
                <div class="stat-label" id="currentTime"></div>
            </div>
            <div class="stat-chart-bg"></div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/quote-request.png" alt="Total Requests" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $total_requests; ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
            <div class="stat-chart-bg"></div>
        </div>
        
        <div class="stat-card pending">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/wall-clock.png" alt="Pending" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $pending_requests; ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-chart-bg"></div>
        </div>
        
        <div class="stat-card approved">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/check.png" alt="Approved" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $approved_requests; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-chart-bg"></div>
        </div>
        
        <div class="stat-card rejected">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/decline.png" alt="Rejected" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $rejected_requests; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-chart-bg"></div>
        </div>
    </div>
    
    <div class="content-grid">
        <!-- Recent Requests -->
        <div class="main-card recent-requests-card" style="position: relative; border-top: 4px solid #ff0000dd; overflow: hidden;">
            <div class="card-header" style="border-bottom: 2px solid #ff000094;">
                <div class="card-title-wrapper">
                    <img src="<?php echo BASE_URL; ?>/assets/css/icons/folder.png" alt="Recent" style="width: 28px; height: 28px; object-fit: contain;">
                    <h2 class="card-title">Recent Requisition Requests</h2>
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/all_requests.php" class="view-all-link">View All <span class="arrow">→</span></a>
            </div>
            
            <?php if (empty($recent_requests)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Requests Yet</h3>
                    <p>There are currently no requisition requests in the system.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>RF Number</th>
                                <th>Requester</th>
                                <th>Department</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Level</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_requests as $request): ?>
                                <tr>
                                    <td>
                                        <span class="rf-number-badge">
                                            <?php echo htmlspecialchars($request['rf_control_number']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar"><?php echo strtoupper(substr($request['requester_name'], 0, 1)); ?></div>
                                            <span><?php echo htmlspecialchars($request['requester_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="dept-badge"><?php echo htmlspecialchars($request['department']); ?></span></td>
                                    <td><?php echo $request['item_count']; ?></td>
                                    <td><?php echo getStatusBadge($request['status']); ?></td>
                                    <td>
                                        <div class="level-indicator">
                                            <span class="level-dot"></span>
                                            Level <?php echo $request['current_approval_level']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo formatDate($request['created_at'], 'M d, Y'); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/approver/view_request.php?id=<?php echo $request['id']; ?>" 
                                           class="btn-icon" title="View Details">
                                            <img src="<?php echo BASE_URL; ?>/assets/css/icons/eye.png" alt="View" class="action-icon">
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="side-panel">
            <div class="main-card actions-card" style="position: relative; border-top: 4px solid #2ecc71; overflow: hidden;">
                <div class="card-header" style="border-bottom: 2px solid #2ecc71;">
                    <h2 class="card-title">
                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/cursor.png" alt="Quick Actions" style="width: 24px; height: 24px; object-fit: contain; vertical-align: middle; margin-right: 8px;">
                        Quick Actions
                    </h2>
                </div>
                
                <div class="quick-actions-grid">
                    <a href="<?php echo BASE_URL; ?>/admin/all_requests.php" class="action-tile">
                        <div class="tile-icon"><img src="<?php echo BASE_URL; ?>/assets/css/icons/open-folder.png" alt="All Requests" style="width: 32px; height: 32px; object-fit: contain;"></div>
                        <div class="tile-content">
                            <span class="tile-title">All Requests</span>
                            <span class="tile-desc">View full history</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/admin/manage_approvers.php" class="action-tile">
                        <div class="tile-icon"><img src="<?php echo BASE_URL; ?>/assets/css/icons/group.png" alt="Approvers" style="width: 32px; height: 32px; object-fit: contain;"></div>
                        <div class="tile-content">
                            <span class="tile-title">Approvers</span>
                            <span class="tile-desc">Manage permissions</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="action-tile">
                        <div class="tile-icon"><img src="<?php echo BASE_URL; ?>/assets/css/icons/google-analytics.png" alt="Reports" style="width: 32px; height: 32px; object-fit: contain;"></div>
                        <div class="tile-content">
                            <span class="tile-title">Reports</span>
                            <span class="tile-desc">Generate analytics</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/public/request_form.php" class="action-tile primary">
                        <div class="tile-icon"><img src="<?php echo BASE_URL; ?>/assets/css/icons/new-page.png" alt="New Request" style="width: 32px; height: 32px; object-fit: contain; filter: brightness(0) invert(1);"></div>
                        <div class="tile-content">
                            <span class="tile-title">New Request</span>
                            <span class="tile-desc">Create requisition</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="main-card info-card" style="position: relative; border-top: 4px solid #27ae60; overflow: hidden; margin-top: 1.5rem;">
                <div class="card-header" style="border-bottom: 2px solid #27ae60;">
                    <h2 class="card-title">System Status</h2>
                </div>
                <div class="system-status-list">
                    <div class="status-item">
                        <span class="status-label">Database</span>
                        <span class="status-value online">Online</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Last Backup</span>
                        <span class="status-value">Today, 02:00 AM</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Version</span>
                        <span class="status-value">v1.2.0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --card-bg: #ffffff;
        --bg-color: #f8f9fa;
        --text-primary: #2c3e50;
        --text-secondary: #6c757d;
        --border-color: #e9ecef;
        --hover-bg: #f8f9fa;
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
        --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
    }

    body {
        background-color: var(--bg-color);
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Header Styles */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .dashboard-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #000000;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .subtitle {
        color: #2c3e50;
        font-size: 1.1rem;
        margin: 0;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        font-weight: 500;
    }

    .date-badge {
        background-color: var(--white);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-secondary);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .stat-icon-img {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }

    .stat-card.total .stat-icon-wrapper { color: #3498db; }
    .stat-card.pending .stat-icon-wrapper { color: #f1c40f; }
    .stat-card.approved .stat-icon-wrapper { color: #2ecc71; }
    .stat-card.rejected .stat-icon-wrapper { color: #e74c3c; }
    
    .stat-card.total {
        background: #ffffff;
        border: 4px solid #3498db;
    }
    
    .stat-card.pending {
        background: #ffffff;
        border: 4px solid #f1c40f;
    }
    
    .stat-card.approved {
        background: #ffffff;
        border: 4px solid #2ecc71;
    }
    
    .stat-card.rejected {
        background: #ffffff;
        border: 4px solid #e74c3c;
    }

    .stat-details {
        z-index: 1;
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: #000000;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    .main-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .card-title-wrapper {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-icon {
        font-size: 1.25rem;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .view-all-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: gap 0.2s ease;
    }

    .view-all-link:hover {
        gap: 0.5rem;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table th {
        text-align: left;
        padding: 1rem;
        color: white;
        background-color: var(--primary-color);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-dark);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .user-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #4a6cf7;
        color: white;
        font-weight: 600;
        font-size: 14px;
        flex-shrink: 0;
        overflow: hidden;
    }
    
    .modern-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .modern-table tr:last-child td {
        border-bottom: none;
    }

    .modern-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: var(--hover-bg);
    }

    .rf-number-badge {
        font-family: 'Courier New', monospace;
        background-color: var(--bg-color);
        padding: 0.35rem 0.6rem;
        border-radius: var(--radius-sm);
        font-weight: 700;
        color: var(--primary-color);
        font-size: 0.9rem;
        border: 1px solid var(--border-color);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .dept-badge {
        background-color: rgba(0,0,0,0.05);
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.2s ease;
        background-color: transparent;
    }

    .action-icon {
        width: 18px;
        height: 18px;
        object-fit: contain;
        transition: filter 0.2s ease;
    }

    .btn-icon:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-icon:hover .action-icon {
        filter: brightness(0) invert(1);
    }

    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .action-tile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background-color: var(--bg-color);
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .action-tile:hover {
        background-color: white;
        box-shadow: var(--shadow-md);
        transform: translateX(5px);
        border-color: var(--border-color);
        text-decoration: none;
    }

    .action-tile.primary {
        background-color: #27ae60;
        color: white;
    }

    .action-tile.primary:hover {
        background-color: #229954;
        color: white;
    }

    .action-tile.primary .tile-title,
    .action-tile.primary .tile-desc {
        color: white;
    }

    .tile-icon {
        font-size: 1.5rem;
    }

    .tile-content {
        display: flex;
        flex-direction: column;
    }

    .tile-title {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1rem;
    }

    .tile-desc {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    /* System Status */
    .system-status-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    .status-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .status-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .status-value {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .status-value.online {
        color: var(--success-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-value.online::before {
        content: '';
        width: 8px;
        height: 8px;
        background-color: var(--success-color);
        border-radius: 50%;
        display: block;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .side-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }
        
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .stat-card.datetime {
        padding: 0.75rem 1rem;
        max-width: 240px;
    }
    
    .stat-card.datetime .stat-icon-wrapper { 
        background-color: rgba(52, 152, 219, 0.1); 
        color: #3498db;
        width: 40px;
        height: 40px;
    }
    
    .stat-card.datetime .stat-icon-img {
        width: 22px;
        height: 22px;
    }
    
    .stat-card.datetime .stat-value {
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
    }
    
    .stat-card.datetime .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
    }
</style>

<script>
function updateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`;
    
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Update time immediately and then every second
updateTime();
setInterval(updateTime, 1000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
