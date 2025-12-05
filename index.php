<?php
$page_title = 'Home - LEYECO III Requisition System';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Requisition System</h1>
            <p class="hero-subtitle">Streamlined Material Request and Approval Management</p>
            <p>Submit requisition requests, track their progress, and manage approvals efficiently.</p>
            
            <div class="hero-actions">
                <a href="<?php echo BASE_URL; ?>/public/request_form.php" class="btn btn-primary btn-lg">
                    📝 Submit New Request
                </a>
                <a href="<?php echo BASE_URL; ?>/public/track_request.php" class="btn btn-outline btn-lg">
                    🔍 Track Request
                </a>
            </div>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="features-section">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Streamline your requisition process in just three simple steps</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/contact-form.png" alt="Submit Request" class="feature-img">
                    </div>
                    <div class="feature-content">
                        <h3>1. Submit Request</h3>
                        <p>Fill out the requisition form with your material requirements. No login required!</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/approval.png" alt="Approval Process" class="feature-img">
                    </div>
                    <div class="feature-content">
                        <h3>2. Approval Process</h3>
                        <p>Your request goes through a 5-level sequential approval workflow for proper authorization.</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="<?php echo BASE_URL; ?>/assets/css/icons/search-file.png" alt="Track Progress" class="feature-img">
                    </div>
                    <div class="feature-content">
                        <h3>3. Track Progress</h3>
                        <p>Monitor your request status in real-time using your unique RF Control Number.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Approval Workflow Section -->
    <div class="workflow-section">
        <h2 class="text-center mb-4">Approval Workflow</h2>
        
        <div class="workflow-timeline">
            <?php foreach (APPROVAL_LEVELS as $level => $description): ?>
                <?php 
                    $parts = explode(' - ', $description);
                    $action = $parts[0];
                    $role = $parts[1] ?? '';
                ?>
                <div class="workflow-step">
                    <div class="workflow-number"><?php echo $level; ?></div>
                    <div class="workflow-details">
                        <h4 class="mb-1"><?php echo $action; ?></h4>
                        <p class="text-muted mb-0"><?php echo $role; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Info Section -->
    <div class="info-section">
        <div class="card">
            <h3>📌 Important Information</h3>
            <ul>
                <li><strong>No Login Required:</strong> Employees can submit and track requests without creating an account.</li>
                <li><strong>Unique RF Number:</strong> Each request receives a unique control number (Format: RF-YYYYMMDD-XXXX).</li>
                <li><strong>Sequential Approval:</strong> All 5 levels must approve in order for the request to be completed.</li>
                <li><strong>Real-time Tracking:</strong> Check your request status anytime using your RF Control Number.</li>
                <li><strong>Approver Access:</strong> Authorized approvers can login to review and process requests at their level.</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: var(--white);
        padding: 70px 40px;
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        margin-bottom: 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.1) 0%, transparent 70%);
        animation: pulse 8s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
    }
    
    .hero-content h1 {
        font-size: 48px;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.3);
        background: linear-gradient(to right, var(--white), var(--secondary-light));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .hero-subtitle {
        font-size: 20px;
        margin-bottom: 35px;
        opacity: 0.95;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-actions {
        display: flex;
        gap: var(--spacing-md);
        justify-content: center;
        margin-top: var(--spacing-xl);
    }
    
    .hero-actions .btn-primary {
        background-color: var(--primary-color);
        border: 2px solid var(--primary-color);
        color: white;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .hero-actions .btn-primary:hover {
        background-color: var(--secondary-color) !important;
        border-color: var(--secondary-color) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }
    
    .hero-actions .btn-outline {
        background: transparent;
        border: 2px solid white;
        color: white;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .hero-actions .btn-outline:hover {
        background-color: var(--secondary-color) !important;
        border-color: var(--secondary-color) !important;
        color: var(--primary-dark) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }
    
    .features-section {
        padding: 3rem 0 5rem 0;
        background-color: var(--white);
        margin-bottom: var(--spacing-xxl);
        border-radius: 0 0 var(--radius-xl) var(--radius-xl);
        box-shadow: var(--shadow-lg);
    }
    
    .section-title {
        text-align: center;
        font-size: 2.25rem;
        color: var(--primary-dark);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 4px;
        background: var(--secondary-color);
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
    }
    
    .section-subtitle {
        text-align: center;
        color: var(--text-gray);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto 3rem;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .feature-card {
        background-color: var(--white);
        padding: var(--spacing-xl);
        border-radius: var(--radius-lg);
        text-align: center;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid var(--light-gray);
        color: var(--dark);
    }
    
    .feature-card h3 {
        color: var(--primary-color);
    }
    
    .feature-card p {
        color: var(--dark-gray);
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }
    
    
    .workflow-section {
        margin-bottom: var(--spacing-xxl);
        background-color: var(--white);
        padding: var(--spacing-xl);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
    }
    
    .workflow-section h2 {
        color: var(--primary-color);
        margin-bottom: var(--spacing-xl);
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }
    
    .workflow-section h2::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 4px;
        background: var(--secondary-color);
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 2px;
    }
    
    .workflow-timeline {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }
    
    /* Feature Icons */
    .feature-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .feature-icon img.feature-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .workflow-step {
        background-color: var(--white);
        border: 1px solid var(--light-gray);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        color: var(--dark);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
    }
    
    .workflow-step h4 {
        color: var(--primary-color);
        margin-bottom: var(--spacing-xs);
    }
    
    .workflow-step p {
        color: var(--dark-gray);
        margin-bottom: 0;
    }
    
    .workflow-number {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .workflow-details h4 {
        color: var(--primary-color);
        margin: 0 0 var(--spacing-xs) 0;
    }
    
    .workflow-details p {
        margin: 0;
    }
    
    .info-section ul {
        list-style-position: inside;
        line-height: 2;
    }
    
    .info-section li {
        margin-bottom: var(--spacing-sm);
    }
    
    @media (max-width: 768px) {
        .hero-content h1 {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
        }
        
        .hero-actions {
            flex-direction: column;
        }
        
        .workflow-step {
            flex-direction: column;
            text-align: center;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
        }
        
        .workflow-number {
            margin: 0 auto 0.75rem auto;
            width: 45px;
            height: 45px;
            font-size: 1.25rem;
        }
        
        .workflow-details {
            width: 100%;
        }
        
        .workflow-details h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .workflow-details p {
            font-size: 0.9rem;
            line-height: 1.4;
        }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
