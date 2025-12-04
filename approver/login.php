<?php
$page_title = 'Staff Login - LEYECO III Requisition System';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirectTo(BASE_URL . '/admin/dashboard.php');
    } else {
        redirectTo(BASE_URL . '/approver/dashboard.php');
    }
}
?>

<div class="container">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/logoL3iii.webp" alt="LEYECO III Logo" class="login-logo">
                <h2>Staff Login</h2>
                <p>Enter your credentials to access the dashboard</p>
            </div>
            
            <form id="loginForm" method="POST" action="<?php echo BASE_URL; ?>/api/authenticate.php">
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="your.email@leyeco3.com"
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="required">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Enter your password">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span>Remember me</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        Login
                    </button>
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>/" class="small-link">
                            ← Back to Home
                        </a>
                    </div>
                </div>
            </form>
            
            <div class="login-footer">
                <!-- Footer content removed as it's been moved -->
            </div>
        </div>
    </div>
</div>

<style>
    .login-container {
        max-width: 500px;
        margin: var(--spacing-xxl) auto;
    }
    
    .login-card {
        background-color: var(--white);
        padding: var(--spacing-xxl);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
    }
    
    .login-header {
        text-align: center;
        margin-bottom: var(--spacing-xl);
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .login-logo {
        max-width: 60px;
        height: auto;
        margin-bottom: var(--spacing-md);
    }
    
    .form-actions {
        margin-top: var(--spacing-lg);
    }
    
    .btn-block {
        display: block;
        width: 100%;
    }
    
    .small-link {
        font-size: 0.9em;
        color: var(--gray);
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .small-link:hover {
        color: var(--primary-color);
        text-decoration: underline;
    }
    
    .mt-3 {
        margin-top: 1rem;
    }
    
    .login-header h2 {
        color: var(--primary-color);
        margin-bottom: var(--spacing-sm);
    }
    
    .login-header p {
        color: var(--gray);
        margin: 0;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        cursor: pointer;
    }
    
    .checkbox-label input[type="checkbox"] {
        width: auto;
        cursor: pointer;
    }
    
    .login-footer {
        margin-top: var(--spacing-xl);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--light-gray);
        text-align: center;
    }
    
    .login-footer p {
        margin-bottom: var(--spacing-sm);
        font-size: 0.875rem;
        color: var(--gray);
    }
    
    .login-footer code {
        background-color: var(--lighter-gray);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-family: 'Courier New', monospace;
        color: var(--primary-color);
    }
    
    @media (max-width: 768px) {
        .login-card {
            padding: var(--spacing-lg);
        }
    }
</style>

<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            email: formData.get('email'),
            password: formData.get('password'),
            remember_me: formData.get('remember_me') ? true : false
        };
        
        Loading.show();
        
        try {
            const response = await Ajax.post('<?php echo BASE_URL; ?>/api/authenticate.php', data);
            
            Loading.hide();
            
            if (response.success) {
                Toast.show('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = response.data.redirect_url;
                }, 1000);
            } else {
                Toast.show(response.message || 'Login failed', 'error');
            }
        } catch (error) {
            Loading.hide();
            console.error('Login error:', error);
            Toast.show('An error occurred during login', 'error');
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
