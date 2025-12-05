<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAdmin();

$page_title = 'Reports - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Get statistics
$stats = [];

// Total requests by status
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM requisition_requests 
    GROUP BY status
");
while ($row = $stmt->fetch()) {
    $stats['by_status'][$row['status']] = $row['count'];
}

// Requests by department
$stmt = $pdo->query("
    SELECT department, COUNT(*) as count 
    FROM requisition_requests 
    GROUP BY department 
    ORDER BY count DESC
");
$stats['by_department'] = $stmt->fetchAll();

// Requests by month (last 6 months)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM requisition_requests
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month DESC
");
$stats['by_month'] = $stmt->fetchAll();

// Approval statistics
$stmt = $pdo->query("
    SELECT 
        approval_level,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM approvals
    GROUP BY approval_level
    ORDER BY approval_level
");
$stats['by_level'] = $stmt->fetchAll();

// Recent activity
$stmt = $pdo->query("
    SELECT r.*, 
           (SELECT COUNT(*) FROM requisition_items WHERE requisition_id = r.id) as item_count
    FROM requisition_requests r
    ORDER BY r.updated_at DESC
    LIMIT 10
");
$recent_activity = $stmt->fetchAll();
?>

<div class="page-container">
    <div class="page-header">
        <div class="header-content">
            <h1>
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/google-analytics.png" alt="System Analytics" style="width: 36px; height: 36px; object-fit: contain; vertical-align: middle; margin-right: 12px;">
                System Analytics
            </h1>
            <p class="subtitle">Comprehensive overview of system performance and usage.</p>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/quote-request.png" alt="Total Requests" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value">
                    <?php echo array_sum($stats['by_status'] ?? [0]); ?>
                </div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        
        <div class="stat-card pending">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/wall-clock.png" alt="Pending" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['by_status']['pending'] ?? 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        
        <div class="stat-card approved">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/check.png" alt="Approved" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['by_status']['approved'] ?? 0; ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        
        <div class="stat-card rejected">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/decline.png" alt="Rejected" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['by_status']['rejected'] ?? 0; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>
    
    <div class="reports-grid">
        <!-- Requests by Department -->
        <div class="report-card department-card">
            <div class="card-header">
                <h3 class="card-title">Requests by Department</h3>
            </div>
            
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Requests</th>
                            <th style="width: 50%;">Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = array_sum(array_column($stats['by_department'], 'count'));
                        foreach ($stats['by_department'] as $dept): 
                            $percentage = $total > 0 ? round(($dept['count'] / $total) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td><span class="dept-name"><?php echo htmlspecialchars($dept['department']); ?></span></td>
                                <td class="count-cell"><?php echo $dept['count']; ?></td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo $percentage; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Monthly Trend -->
        <div class="report-card trend-card">
            <div class="card-header">
                <h3 class="card-title">Monthly Trend</h3>
                <span class="subtitle-small">Last 6 Months</span>
            </div>
            
            <div class="trend-list">
                <?php foreach ($stats['by_month'] as $month): 
                    $max_count = !empty($stats['by_month']) ? max(array_column($stats['by_month'], 'count')) : 1;
                    $height_percent = ($month['count'] / $max_count) * 100;
                ?>
                    <div class="trend-item">
                        <div class="trend-bar-container">
                            <div class="trend-bar" style="height: <?php echo $height_percent; ?>%">
                                <span class="trend-value"><?php echo $month['count']; ?></span>
                            </div>
                        </div>
                        <span class="trend-label"><?php echo date('M', strtotime($month['month'] . '-01')); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Approval Level Statistics -->
    <div class="report-card full-width">
        <div class="card-header">
            <h3 class="card-title">Approval Workflow Performance</h3>
        </div>
        
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Role</th>
                        <th>Total Processed</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                        <th>Pending</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['by_level'] as $level): ?>
                        <tr>
                            <td>
                                <span class="level-badge level-<?php echo $level['approval_level']; ?>">
                                    Level <?php echo $level['approval_level']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(APPROVAL_LEVELS[$level['approval_level']]); ?></td>
                            <td><strong><?php echo $level['total']; ?></strong></td>
                            <td><span class="status-pill success"><?php echo $level['approved']; ?></span></td>
                            <td><span class="status-pill danger"><?php echo $level['rejected']; ?></span></td>
                            <td><span class="status-pill warning"><?php echo $level['pending']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="report-card full-width">
        <div class="card-header">
            <h3 class="card-title">Recent System Activity</h3>
        </div>
        
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>RF Number</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity as $activity): ?>
                        <tr>
                            <td>
                                <span class="rf-number-badge">
                                    <?php echo htmlspecialchars($activity['rf_control_number']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?php echo strtoupper(substr($activity['requester_name'], 0, 1)); ?></div>
                                    <span><?php echo htmlspecialchars($activity['requester_name']); ?></span>
                                </div>
                            </td>
                            <td><span class="dept-badge"><?php echo htmlspecialchars($activity['department']); ?></span></td>
                            <td><?php echo getStatusBadge($activity['status']); ?></td>
                            <td><?php echo formatDate($activity['updated_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
    }

    body {
        background-color: var(--bg-color);
    }

    .page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #000000;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        margin: 0;
        padding-left: 48px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background-color: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: var(--shadow-sm);
    }

    .btn-back:hover {
        color: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateX(-3px);
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
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
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

    .stat-card.total .stat-icon-wrapper { background-color: rgba(52, 152, 219, 0.1); color: #3498db; }
    .stat-card.pending .stat-icon-wrapper { background-color: rgba(241, 196, 15, 0.1); color: #f1c40f; }
    .stat-card.approved .stat-icon-wrapper { background-color: rgba(46, 204, 113, 0.1); color: #2ecc71; }
    .stat-card.rejected .stat-icon-wrapper { background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; }
    
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

    /* Reports Grid */
    .reports-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .report-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .report-card.full-width {
        grid-column: 1 / -1;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .subtitle-small {
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
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
        padding: 1rem 1.5rem;
        color: white;
        background-color: var(--primary-color);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-dark);
    }

    .modern-table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .modern-table tbody tr:last-child td {
        border-bottom: none;
    }

    .modern-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: var(--hover-bg);
    }

    /* Progress Bar */
    .progress-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .progress-bar {
        flex: 1;
        height: 8px;
        background-color: var(--bg-color);
        border-radius: 50px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background-color: var(--primary-color);
        border-radius: 50px;
    }

    .progress-text {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        width: 40px;
        text-align: right;
    }

    /* Trend Chart */
    .trend-list {
        display: flex;
        justify-content: space-around;
        align-items: flex-end;
        height: 300px;
        padding: 2rem 1rem 1rem;
    }

    .trend-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        height: 100%;
        width: 100%;
    }

    .trend-bar-container {
        flex: 1;
        width: 40px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background-color: rgba(0,0,0,0.02);
        border-radius: 50px;
        position: relative;
    }

    .trend-bar {
        width: 100%;
        background-color: var(--primary-color);
        border-radius: 50px;
        position: relative;
        transition: height 0.5s ease;
        min-height: 10px;
    }

    .trend-value {
        position: absolute;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--text-primary);
    }

    .trend-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
    }

    /* Badges */
    .level-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: var(--bg-color);
        color: var(--text-secondary);
    }

    .status-pill {
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-pill.success { background-color: rgba(46, 204, 113, 0.1); color: var(--success-color); }
    .status-pill.danger { background-color: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
    .status-pill.warning { background-color: rgba(241, 196, 15, 0.1); color: #f1c40f; }

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

    @media (max-width: 1024px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
