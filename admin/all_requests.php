<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAdmin();

$page_title = 'All Requests - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'approved', 'rejected', 'completed'])) {
    $where_conditions[] = "r.status = :status";
    $params['status'] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(r.rf_control_number LIKE :search OR r.requester_name LIKE :search OR r.department LIKE :search)";
    $params['search'] = "%{$search}%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM requisition_requests r {$where_clause}");
$count_stmt->execute($params);
$total_records = $count_stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Get requests
$params['limit'] = $per_page;
$params['offset'] = $offset;

$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM requisition_items WHERE requisition_id = r.id) as item_count
    FROM requisition_requests r
    {$where_clause}
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>

<div class="page-container">
    <div class="page-header">
        <div class="header-content">
            <h1>All Requisition Requests</h1>
            <p class="subtitle">Manage and track all requisition requests in the system.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn-back">
            <span class="icon">←</span> Back to Dashboard
        </a>
    </div>
    
    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group search-group">
                <label for="search">Search Requests</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔍</span>
                    <input type="text" id="search" name="search" placeholder="RF Number, Requester, Department..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            
            <div class="filter-group status-group">
                <label for="status">Filter by Status</label>
                <div class="select-wrapper">
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <span class="select-arrow">▼</span>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Apply Filters</button>
                <?php if ($search || $status_filter): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/all_requests.php" class="btn-reset">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Results -->
    <div class="results-card">
        <div class="card-header">
            <h3 class="card-title">
                Request List <span class="count-badge"><?php echo $total_records; ?></span>
            </h3>
            <div class="pagination-summary">
                Showing <?php echo count($requests); ?> items
            </div>
        </div>
        
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Requests Found</h3>
                <p>No requisition requests match your current filters.</p>
                <a href="<?php echo BASE_URL; ?>/admin/all_requests.php" class="btn-reset-link">Clear all filters</a>
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
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
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
                                <td><?php echo $request['item_count']; ?> item(s)</td>
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
                                       class="btn-view">
                                        View Details
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
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="page-link prev">
                                ← Previous
                            </a>
                        <?php endif; ?>
                        
                        <div class="page-info">
                            Page <strong><?php echo $page; ?></strong> of <?php echo $total_pages; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="page-link next">
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
        margin-bottom: 2rem;
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

    .input-wrapper, .select-wrapper {
        position: relative;
    }

    .input-wrapper input, .select-wrapper select {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        color: var(--text-primary);
        background-color: var(--bg-color);
        transition: all 0.2s ease;
    }

    .select-wrapper select {
        padding-left: 1rem;
        appearance: none;
        cursor: pointer;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
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

    .input-wrapper input:focus, .select-wrapper select:focus {
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

    .btn-view {
        padding: 0.5rem 1rem;
        background-color: transparent;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-view:hover {
        background-color: var(--primary-color);
        color: white;
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

    .btn-reset-link {
        display: inline-block;
        margin-top: 1rem;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .btn-reset-link:hover {
        text-decoration: underline;
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
