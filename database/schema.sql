-- LEYECO III Requisition Management System
-- Database Schema

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS approvals;
DROP TABLE IF EXISTS requisition_items;
DROP TABLE IF EXISTS requisition_requests;
DROP TABLE IF EXISTS approvers;

-- Table: approvers
-- Stores approver user accounts with authentication
CREATE TABLE approvers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    approval_level INT NOT NULL CHECK (approval_level BETWEEN 1 AND 5),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_approval_level (approval_level),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: requisition_requests
-- Main table for requisition requests
CREATE TABLE requisition_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rf_control_number VARCHAR(50) NOT NULL UNIQUE,
    requester_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    purpose TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    current_approval_level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rf_control_number (rf_control_number),
    INDEX idx_status (status),
    INDEX idx_current_approval_level (current_approval_level),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: requisition_items
-- Line items for each requisition request
CREATE TABLE requisition_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT NOT NULL,
    quantity INT NOT NULL,
    unit VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    warehouse_inventory VARCHAR(100) DEFAULT NULL,
    balance_for_purchase VARCHAR(100) DEFAULT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requisition_id) REFERENCES requisition_requests(id) ON DELETE CASCADE,
    INDEX idx_requisition_id (requisition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: approvals
-- Tracks approval workflow for each request
CREATE TABLE approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT NOT NULL,
    approval_level INT NOT NULL CHECK (approval_level BETWEEN 1 AND 5),
    approver_role VARCHAR(100) NOT NULL,
    approver_name VARCHAR(100),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requisition_id) REFERENCES requisition_requests(id) ON DELETE CASCADE,
    INDEX idx_requisition_id (requisition_id),
    INDEX idx_approval_level (approval_level),
    INDEX idx_status (status),
    UNIQUE KEY unique_requisition_level (requisition_id, approval_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
