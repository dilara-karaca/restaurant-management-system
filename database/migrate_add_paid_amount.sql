-- Migration: Add paid_amount column to Orders table
-- This tracks the amount paid when payment is made, so we can calculate remaining balance

-- Check if column exists before adding
SET @dbname = DATABASE();
SET @tablename = 'Orders';
SET @columnname = 'paid_amount';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT 0 AFTER total_amount')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing orders: if payment_method is set, set paid_amount = total_amount
UPDATE Orders 
SET paid_amount = total_amount 
WHERE payment_method IS NOT NULL AND payment_method != '' AND (paid_amount IS NULL OR paid_amount = 0);

