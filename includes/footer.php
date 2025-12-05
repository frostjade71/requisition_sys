    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo" style="display: flex; align-items: center; gap: 10px; margin-bottom: 0.5rem;">
                        <img src="<?php echo BASE_URL; ?>/assets/images/logoL3iii.webp" alt="LEYECO III Logo" style="height: 30px; width: auto;">
                        <h4 style="margin: 0;">LEYECO III</h4>
                    </div>
                    <p>Leyte III Electric Cooperative, Inc.</p>
                    <p><i>Lighting Houses, Lighting Homes, Lighting Hopes</i></p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/request_form.php">Submit Request</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/track_request.php">Track Request</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/approver/login.php">Staff Login</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <p>📧 Email: https://www.leyeco3.com/</p>
                    <p>📞 Phone: +639173049794</p>
                    <p>📍 Tunga, Leyte, Philippines</p>
                </div>
            </div>
            
            <div class="footer-bottom" style="font-size: 0.85rem; margin-top: var(--spacing-md);">
                <p style="margin-bottom: 0.5rem;">&copy; <?php echo date('Y'); ?> LEYECO III. All rights reserved.</p>
                <p style="margin: 0;">Requisition Management System v1.0</p>
            </div>
        </div>
    </footer>
    
    <style>
        .main-content {
            min-height: calc(100vh - 200px);
            padding: var(--spacing-xl) 0;
        }
        
        .footer {
            background: linear-gradient(135deg, var(--dark), var(--dark-gray));
            color: var(--white);
            padding: var(--spacing-lg) 0 var(--spacing-md);
            margin-top: var(--spacing-lg);
            font-size: 0.9rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .footer-section h4 {
            color: var(--secondary-color);
            margin-bottom: var(--spacing-sm);
            font-size: 1rem;
        }
        
        .footer-section p {
            margin-bottom: var(--spacing-sm);
            color: var(--light-gray);
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-section ul li {
            margin-bottom: var(--spacing-sm);
        }
        
        .footer-section ul li a {
            color: var(--light-gray);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .footer-section ul li a:hover {
            color: var(--secondary-color);
        }
        
        .footer-bottom {
            border-top: 1px solid var(--dark-gray);
            padding-top: var(--spacing-lg);
            text-align: center;
        }
        
        .footer-bottom p {
            margin-bottom: var(--spacing-xs);
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: var(--spacing-lg);
            }
        }
    </style>
    
    <!-- JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo BASE_URL . $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
