    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>LEYECO III Electric Company</h4>
                    <p>Leyte III Electric Cooperative, Inc.</p>
                    <p>Serving the communities with reliable electric service</p>
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
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> LEYECO III Electric Company. All rights reserved.</p>
                <p>Requisition Management System v1.0</p>
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
            padding: var(--spacing-xxl) 0 var(--spacing-lg);
            margin-top: var(--spacing-xxl);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }
        
        .footer-section h4 {
            color: var(--secondary-color);
            margin-bottom: var(--spacing-md);
            font-size: 1.125rem;
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
