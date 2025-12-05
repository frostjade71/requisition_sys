-- LEYECO III Requisition Management System
-- Sample Data

-- Insert Approvers (5 levels + 1 admin)
-- Password for all accounts: password123 (hashed with bcrypt)
INSERT INTO approvers (name, role, approval_level, email, password, is_admin) VALUES
('Juan Dela Cruz', 'Section Head', 1, 'juan.delacruz@leyeco3.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Maria Santos', 'Warehouse Section Head', 2, 'maria.santos@leyeco3.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Pedro Reyes', 'Budget Officer', 3, 'pedro.reyes@leyeco3.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Ana Garcia', 'Internal Auditor', 4, 'ana.garcia@leyeco3.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Roberto Fernandez', 'General Manager', 5, 'roberto.fernandez@leyeco3.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Admin User', 'System Administrator', 5, 'admin@leyeco3.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insert Sample Requisition Requests
INSERT INTO requisition_requests (rf_control_number, requester_name, department, purpose, status, current_approval_level) VALUES
('RF-20251203-0001', 'Jose Rizal', 'Engineering Department', 'Repair and maintenance of transformer at Barangay San Jose', 'pending', 3),
('RF-20251203-0002', 'Andres Bonifacio', 'Operations Department', 'Installation of new power lines in subdivision area', 'pending', 1),
('RF-20251203-0003', 'Emilio Aguinaldo', 'Technical Services', 'Replacement of damaged electrical poles due to typhoon', 'approved', 5);

-- Insert Sample Requisition Items for Request 1 (RF-20251203-0001)
INSERT INTO requisition_items (requisition_id, quantity, unit, description, warehouse_inventory, balance_for_purchase, remarks) VALUES
(1, 5, 'pcs', 'Electrical Wire 10mm THHN', 3, 2, 'Urgent - needed for transformer repair'),
(1, 2, 'pcs', 'Circuit Breaker 100A', 0, 2, 'Not available in warehouse'),
(1, 10, 'meters', 'Insulated Cable 25mm', 5, 5, 'Additional stock needed');

-- Insert Sample Requisition Items for Request 2 (RF-20251203-0002)
INSERT INTO requisition_items (requisition_id, quantity, unit, description, warehouse_inventory, balance_for_purchase, remarks) VALUES
(2, 100, 'meters', 'Aluminum Wire #2 AWG', 50, 50, 'For subdivision power line installation'),
(2, 15, 'pcs', 'Concrete Pole 35ft', 0, 15, 'Special order required'),
(2, 20, 'pcs', 'Pole Bracket', 10, 10, 'Standard brackets');

-- Insert Sample Requisition Items for Request 3 (RF-20251203-0003)
INSERT INTO requisition_items (requisition_id, quantity, unit, description, warehouse_inventory, balance_for_purchase, remarks) VALUES
(3, 8, 'pcs', 'Wooden Pole 40ft Class 2', 0, 8, 'Emergency replacement'),
(3, 50, 'meters', 'Guy Wire 7mm', 30, 20, 'For pole stabilization'),
(3, 16, 'pcs', 'Pole Anchor', 8, 8, 'Ground anchors for guy wires');

-- Insert Approval Records for Request 1 (Currently at Level 3)
INSERT INTO approvals (requisition_id, approval_level, approver_role, approver_name, status, remarks, approved_at) VALUES
(1, 1, 'Section Head', 'Juan Dela Cruz', 'approved', 'Approved - necessary for transformer repair', '2025-12-03 08:00:00'),
(1, 2, 'Warehouse Section Head', 'Maria Santos', 'approved', 'Inventory checked - partial stock available', '2025-12-03 08:30:00'),
(1, 3, 'Budget Officer', NULL, 'pending', NULL, NULL),
(1, 4, 'Internal Auditor', NULL, 'pending', NULL, NULL),
(1, 5, 'General Manager', NULL, 'pending', NULL, NULL);

-- Insert Approval Records for Request 2 (Currently at Level 1)
INSERT INTO approvals (requisition_id, approval_level, approver_role, approver_name, status, remarks, approved_at) VALUES
(2, 1, 'Section Head', NULL, 'pending', NULL, NULL),
(2, 2, 'Warehouse Section Head', NULL, 'pending', NULL, NULL),
(2, 3, 'Budget Officer', NULL, 'pending', NULL, NULL),
(2, 4, 'Internal Auditor', NULL, 'pending', NULL, NULL),
(2, 5, 'General Manager', NULL, 'pending', NULL, NULL);

-- Insert Approval Records for Request 3 (Fully Approved)
INSERT INTO approvals (requisition_id, approval_level, approver_role, approver_name, status, remarks, approved_at) VALUES
(3, 1, 'Section Head', 'Juan Dela Cruz', 'approved', 'Emergency approval - typhoon damage', '2025-12-02 09:00:00'),
(3, 2, 'Warehouse Section Head', 'Maria Santos', 'approved', 'Inventory checked - purchase required', '2025-12-02 10:00:00'),
(3, 3, 'Budget Officer', 'Pedro Reyes', 'approved', 'Budget allocated for emergency repairs', '2025-12-02 11:00:00'),
(3, 4, 'Internal Auditor', 'Ana Garcia', 'approved', 'Documentation complete', '2025-12-02 14:00:00'),
(3, 5, 'General Manager', 'Roberto Fernandez', 'approved', 'Final approval granted - proceed with procurement', '2025-12-02 15:00:00');
