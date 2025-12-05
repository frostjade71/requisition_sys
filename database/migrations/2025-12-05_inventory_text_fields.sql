-- Migration: Change inventory fields to support text values
-- Date: 2025-12-05
-- Description: Changes warehouse_inventory and balance_for_purchase from INT to VARCHAR(100)
--              to allow free-form text like "N/A", "Out of stock", "Pending delivery", etc.

-- Modify warehouse_inventory column
ALTER TABLE requisition_items 
MODIFY COLUMN warehouse_inventory VARCHAR(100) DEFAULT NULL;

-- Modify balance_for_purchase column
ALTER TABLE requisition_items 
MODIFY COLUMN balance_for_purchase VARCHAR(100) DEFAULT NULL;

-- Note: Existing numeric values will be automatically converted to strings
-- Example: 5 becomes "5", 0 becomes "0"
