<?php
// Load configuration first (before session starts)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

// Start session after configuration is loaded
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LEYECO III Requisition Management System">
    <title><?php echo $page_title ?? APP_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo BASE_URL . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>/" class="navbar-brand">
                <img src="<?php echo BASE_URL; ?>/assets/images/logoL3iii.webp" alt="" class="logo-icon">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo_leyeco3.webp" alt="LEYECO III Logo" class="logo-text">
            </a>
            
            <div class="navbar-menu" id="navbarMenu">
                <ul class="navbar-nav">
                    <?php if (!isLoggedIn()): ?>
                        <!-- Public Navigation -->
                        <li><a href="<?php echo BASE_URL; ?>/" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Home
                        </a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/request_form.php" class="<?php echo $current_page === 'request_form.php' ? 'active' : ''; ?>">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/track_request.php" class="<?php echo $current_page === 'track_request.php' ? 'active' : ''; ?>">
                            <i class="fas fa-search"></i> Track Request
                        </a></li>
                        <li><a href="<?php echo BASE_URL; ?>/approver/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Staff Login
                        </a></li>
                    <?php else: ?>
                        <!-- Authenticated Navigation -->
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="<?php echo BASE_URL; ?>/admin/all_requests.php" class="<?php echo $current_page === 'all_requests.php' ? 'active' : ''; ?>">
                                <i class="fas fa-list"></i> All Requests
                            </a></li>
                            <li><a href="<?php echo BASE_URL; ?>/admin/manage_approvers.php" class="<?php echo $current_page === 'manage_approvers.php' ? 'active' : ''; ?>">
                                <i class="fas fa-users-cog"></i> Manage Approvers
                            </a></li>
                            <li><a href="<?php echo BASE_URL; ?>/admin/reports.php" class="<?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>/approver/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="<?php echo BASE_URL; ?>/approver/history.php" class="<?php echo $current_page === 'history.php' ? 'active' : ''; ?>">
                                <i class="fas fa-history"></i> My History
                            </a></li>
                        <?php endif; ?>
                        
                        <li class="user-menu">
                            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                            <a href="<?php echo BASE_URL; ?>/approver/logout.php" class="btn btn-danger" title="Logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="navbar-toggle" id="navbarToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu = document.getElementById('navbarMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        
        // Toggle mobile menu
        navbarToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navbarMenu.classList.toggle('active');
            mobileMenuOverlay.classList.toggle('active');
            
            // Toggle body scroll when menu is open
            document.body.style.overflow = this.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close menu when clicking on overlay
        mobileMenuOverlay.addEventListener('click', function() {
            navbarToggle.classList.remove('active');
            navbarMenu.classList.remove('active');
            this.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Close menu when clicking on a nav link
        document.querySelectorAll('.navbar-nav a').forEach(link => {
            link.addEventListener('click', function() {
                navbarToggle.classList.remove('active');
                navbarMenu.classList.remove('active');
                mobileMenuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    });
    </script>
    
    <style>
        /* Base styles that might be used elsewhere */
        :root {
            --primary-color: #DC2626;
            --primary-dark: #991B1B;
            --accent-yellow: #FBBF24;
            --white: #FFFFFF;
            --off-white: #FAFAFA;
            --light-gray: #F3F4F6;
            --text-dark: #1F2937;
            --text-gray: #6B7280;
            --border-light: #E5E7EB;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .navbar-nav {
            display: flex;
            list-style: none;
            gap: var(--spacing-lg);
            align-items: center;
        }
        
        .navbar-nav a {
            color: var(--white);
            text-decoration: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            transition: background-color var(--transition-fast);
        }
        
        .navbar-nav a:hover,
        .navbar-nav a.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .user-menu {
            display: flex;
            gap: var(--spacing-md);
            align-items: center;
        }
        
        .user-menu span {
            color: var(--white);
        }
        
        .navbar-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
            cursor: pointer;
        }
        
        .navbar-toggle span {
            width: 25px;
            height: 3px;
            background-color: var(--white);
            border-radius: 2px;
            transition: var(--transition-fast);
        }
        
        @media (max-width: 768px) {
            .navbar-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: var(--primary-dark);
                padding: var(--spacing-md);
            }
            
            .navbar-menu.active {
                display: block;
            }
            
            .navbar-nav {
                flex-direction: column;
                gap: var(--spacing-sm);
            }
            
            .navbar-toggle {
                display: flex;
            }
            
            .user-menu {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('navbarToggle');
            const menu = document.getElementById('navbarMenu');
            
            if (toggle && menu) {
                toggle.addEventListener('click', function() {
                    menu.classList.toggle('active');
                });
            }
        });
    </script>
    
    <!-- Main Content -->
    <main class="main-content">
