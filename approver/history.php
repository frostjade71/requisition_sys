<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAuth();

$page_title = 'My Approval History - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Get current user's info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name']; // Get the user's name from session

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query - filter by approver_name instead of approver_id
$where_conditions = ["a.approver_name = :user_name", "a.status != 'pending'"]; // Only show processed items
$params = ['user_name' => $user_name];

if ($status_filter && in_array($status_filter, ['approved', 'rejected'])) {
    $where_conditions[] = "a.status = :status";
    $params['status'] = $status_filter;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total count
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM approvals a
    {$where_clause}
");
$count_stmt->execute($params);
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Get approval history
$params['limit'] = $per_page;
$params['offset'] = $offset;

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        r.rf_control_number,
        r.requester_name,
        r.department,
        r.status as request_status,
        (SELECT COUNT(*) FROM requisition_items WHERE requisition_id = r.id) as item_count
    FROM approvals a
    JOIN requisition_requests r ON a.requisition_id = r.id
    {$where_clause}
    ORDER BY a.updated_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$history = $stmt->fetchAll();

// Get statistics - also filter by approver_name
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM approvals
    WHERE approver_name = ?
");
$stats_stmt->execute([$user_name]);
$stats = $stats_stmt->fetch();
?>

<div class="page-container">
    <div class="page-header">
        <div class="header-content">
            <h1>My Approval History</h1>
            <p class="subtitle">Track all requests you have reviewed and processed.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/approver/dashboard.php" class="btn-back">
            <span class="icon">←</span> Back to Dashboard
        </a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/quote-request.png" alt="Total" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Processed</div>
            </div>
        </div>
        
        <div class="stat-card approved">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/check.png" alt="Approved" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        
        <div class="stat-card rejected">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/decline.png" alt="Rejected" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['rejected']; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
        
        <div class="stat-card pending">
            <div class="stat-icon-wrapper">
                <img src="<?php echo BASE_URL; ?>/assets/css/icons/wall-clock.png" alt="Pending" class="stat-icon-img">
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    
    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <label for="status">Filter by Status</label>
                <div class="select-wrapper">
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <span class="select-arrow">▼</span>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Apply Filter</button>
                <?php if ($status_filter): ?>
                    <a href="<?php echo BASE_URL; ?>/approver/history.php" class="btn-reset">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- History Table -->
    <div class="results-card">
        <div class="card-header">
            <h3 class="card-title">
                Approval History <span class="count-badge"><?php echo $total_records; ?></span>
            </h3>
            <div class="pagination-summary">
                Showing <?php echo count($history); ?> items
            </div>
        </div>
        
        <?php if (empty($history)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No History Found</h3>
                <p>You haven't processed any requests yet.</p>
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
                            <th>Your Decision</th>
                            <th>Request Status</th>
                            <th>Date Processed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td>
                                    <span class="rf-number-badge">
                                        <?php echo htmlspecialchars($item['rf_control_number']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar"><?php echo strtoupper(substr($item['requester_name'], 0, 1)); ?></div>
                                        <span><?php echo htmlspecialchars($item['requester_name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="dept-badge"><?php echo htmlspecialchars($item['department']); ?></span></td>
                                <td><?php echo $item['item_count']; ?> item(s)</td>
                                <td>
                                    <?php if ($item['status'] === 'approved'): ?>
                                        <span class="status-badge success">✓ Approved</span>
                                    <?php elseif ($item['status'] === 'rejected'): ?>
                                        <span class="status-badge danger">✗ Rejected</span>
                                    <?php else: ?>
                                        <span class="status-badge warning">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo getStatusBadge($item['request_status']); ?></td>
                                <td><?php echo formatDate($item['updated_at'], 'M d, Y'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/approver/view_request.php?id=<?php echo $item['requisition_id']; ?>" 
                                       class="btn-icon" title="View Details">
                                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/eye.png" alt="View" class="action-icon">
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>" class="page-link prev">
                                ← Previous
                            </a>
                        <?php endif; ?>
                        
                        <div class="page-info">
                            Page <strong><?php echo $page; ?></strong> of <?php echo $total_pages; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>" class="page-link next">
                                Next →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        margin: 0;
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
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 1.25rem;
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
        flex-shrink: 0;
    }

    .stat-icon-img {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }

    .stat-card.total .stat-icon-wrapper { background-color: rgba(52, 152, 219, 0.1); }
    .stat-card.pending .stat-icon-wrapper { background-color: rgba(241, 196, 15, 0.1); }
    .stat-card.approved .stat-icon-wrapper { background-color: rgba(46, 204, 113, 0.1); }
    .stat-card.rejected .stat-icon-wrapper { background-color: rgba(231, 76, 60, 0.1); }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* Filter Card */
    .filter-card {
        background: var(--card-bg);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }

    .filters-form {
        display: flex;
        align-items: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .select-wrapper {
        position: relative;
    }

    .select-wrapper select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        color: var(--text-primary);
        background-color: var(--bg-color);
        appearance: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .select-arrow {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        pointer-events: none;
        font-size: 0.8rem;
    }

    .select-wrapper select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        background-color: white;
    }

    .filter-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-reset {
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        transition: all 0.2s ease;
    }

    .btn-reset:hover {
        background-color: var(--bg-color);
        color: var(--danger-color);
    }

    /* Results Card */
    .results-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        overflow: hidden;
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
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .count-badge {
        background-color: var(--bg-color);
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .pagination-summary {
        color: var(--text-secondary);
        font-size: 0.9rem;
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

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }

    .status-badge.success { background-color: rgba(46, 204, 113, 0.1); color: var(--success-color); }
    .status-badge.danger { background-color: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
    .status-badge.warning { background-color: rgba(241, 196, 15, 0.1); color: #f1c40f; }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
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
    }

    .btn-icon:hover .action-icon {
        filter: brightness(0) invert(1);
    }

    /* Pagination */
    .pagination-container {
        padding: 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 1rem;
        background-color: var(--bg-color);
        padding: 0.5rem;
        border-radius: 50px;
    }

    .page-link {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        text-decoration: none;
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .page-link:hover {
        background-color: white;
        box-shadow: var(--shadow-sm);
        color: var(--primary-color);
    }

    .page-info {
        color: var(--text-secondary);
        font-size: 0.9rem;
        padding: 0 0.5rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 1rem;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
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

        .filters-form {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .filter-actions {
            flex-direction: column;
            width: 100%;
        }

        .btn-primary {
            width: 100%;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
