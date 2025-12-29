-- Migration: Add paid_detail_max_id column to Orders table
-- This tracks the last paid order_detail_id for separating extra items after payment

SET @dbname = DATABASE();
SET @tablename = 'Orders';
SET @columnname = 'paid_detail_max_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL AFTER payment_timestamp')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Initialize paid_detail_max_id for paid orders
UPDATE Orders o
SET paid_detail_max_id = (
  SELECT MAX(od.order_detail_id)
  FROM OrderDetails od
  WHERE od.order_id = o.order_id
)
WHERE o.payment_method IS NOT NULL AND o.payment_method != '' AND o.paid_detail_max_id IS NULL;
