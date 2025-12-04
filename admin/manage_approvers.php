<?php
require_once __DIR__ . '/../middleware/auth.php';
requireAdmin();

$page_title = 'Manage Approvers - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$database = new Database();
$pdo = $database->getConnection();

// Get all approvers
$stmt = $pdo->query("
    SELECT * FROM approvers 
    ORDER BY approval_level ASC, name ASC
");
$approvers = $stmt->fetchAll();
?>

<div class="page-container">
    <div class="page-header">
        <div class="header-content">
            <h1>Manage Approvers</h1>
            <p class="subtitle">Configure system approvers and their authority levels.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn-back">
            <span class="icon">←</span> Back to Dashboard
        </a>
    </div>
    
    <!-- Approvers Grid -->
    <div class="section-container">
        <div class="section-header">
            <h2 class="section-title">System Approvers</h2>
            <span class="badge-count"><?php echo count($approvers); ?> Users</span>
        </div>
        
        <?php if (empty($approvers)): ?>
            <div class="empty-state">
                <div class="empty-icon">👤</div>
                <h3>No Approvers Found</h3>
                <p>There are currently no approvers in the system.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Level</th>
                            <th>Type</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvers as $approver): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar"><?php echo strtoupper(substr($approver['name'], 0, 1)); ?></div>
                                        <span><?php echo htmlspecialchars($approver['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($approver['email']); ?></td>
                                <td><?php echo htmlspecialchars($approver['role']); ?></td>
                                <td>
                                    <span class="level-badge level-<?php echo $approver['approval_level']; ?>">
                                        Level <?php echo $approver['approval_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $approver['is_admin'] ? 'admin' : 'user'; ?>">
                                        <?php echo $approver['is_admin'] ? 'Administrator' : 'Approver'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($approver['created_at'], 'M d, Y'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bottom-grid">
        <!-- Approval Levels Reference -->
        <div class="info-panel">
            <div class="panel-header">
                <h3>Approval Levels Reference</h3>
            </div>
            <div class="levels-list">
                <?php foreach (APPROVAL_LEVELS as $level => $description): ?>
                    <div class="level-item">
                        <div class="level-indicator level-<?php echo $level; ?>">
                            <?php echo $level; ?>
                        </div>
                        <div class="level-details">
                            <span class="level-title">Level <?php echo $level; ?></span>
                            <span class="level-desc"><?php echo htmlspecialchars($description); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- System Info -->
        <div class="info-panel system-info">
            <div class="panel-header">
                <h3>ℹ️ System Information</h3>
            </div>
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Default Password</span>
                    <code class="info-value">password123</code>
                </div>
                <div class="info-row">
                    <span class="info-label">Structure</span>
                    <span class="info-text">Sequential approval flow (1 → 5)</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Admin Access</span>
                    <span class="info-text">Full override capability</span>
                </div>
                <div class="alert-box">
                    <span class="alert-icon">⚠️</span>
                    <p>Ensure all levels have at least one active approver to prevent workflow bottlenecks.</p>
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
        --radius-lg: 16px;
        --radius-md: 12px;
        --radius-sm: 8px;
        
        /* Level Colors */
        --level-1: #3498db;
        --level-2: #9b59b6;
        --level-3: #e67e22;
        --level-4: #2ecc71;
        --level-5: #e74c3c;
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

    /* Section Header */
    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .badge-count {
        background-color: var(--bg-color);
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    /* Table Container */
    .table-container {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 3rem;
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

    .level-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .level-1 { background-color: rgba(52, 152, 219, 0.1); color: var(--level-1); }
    .level-2 { background-color: rgba(155, 89, 182, 0.1); color: var(--level-2); }
    .level-3 { background-color: rgba(230, 126, 34, 0.1); color: var(--level-3); }
    .level-4 { background-color: rgba(46, 204, 113, 0.1); color: var(--level-4); }
    .level-5 { background-color: rgba(231, 76, 60, 0.1); color: var(--level-5); }

    .status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        display: inline-block;
    }

    .status-badge.admin {
        background-color: rgba(46, 204, 113, 0.1);
        color: var(--success-color);
    }

    .status-badge.user {
        background-color: var(--bg-color);
        color: var(--text-secondary);
    }

    /* Bottom Grid */
    .bottom-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    .info-panel {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .panel-header {
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
    }

    .panel-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .levels-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .level-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        background-color: var(--bg-color);
        border-radius: var(--radius-md);
        transition: transform 0.2s ease;
    }

    .level-item:hover {
        transform: translateX(5px);
        background-color: white;
        box-shadow: var(--shadow-sm);
    }

    .level-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .level-indicator.level-1 { background-color: var(--level-1); }
    .level-indicator.level-2 { background-color: var(--level-2); }
    .level-indicator.level-3 { background-color: var(--level-3); }
    .level-indicator.level-4 { background-color: var(--level-4); }
    .level-indicator.level-5 { background-color: var(--level-5); }

    .level-details {
        display: flex;
        flex-direction: column;
    }

    .level-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .level-desc {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    /* System Info */
    .info-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    .info-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .info-value {
        background-color: var(--bg-color);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-family: monospace;
        color: var(--primary-color);
        font-size: 0.9rem;
    }

    .info-text {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .alert-box {
        margin-top: 1rem;
        background-color: rgba(241, 196, 15, 0.1);
        border-radius: var(--radius-md);
        padding: 1rem;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .alert-icon {
        font-size: 1.25rem;
    }

    .alert-box p {
        margin: 0;
        font-size: 0.85rem;
        color: #b7950b;
        line-height: 1.4;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 1rem;
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        border: 1px dashed var(--border-color);
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 1024px) {
        .bottom-grid {
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
        
        .table-container {
            overflow-x: auto;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
