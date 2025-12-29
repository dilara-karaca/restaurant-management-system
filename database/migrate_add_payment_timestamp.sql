-- Migration: Add payment_timestamp to Orders table
-- This tracks when payment was made, so we can find items added after payment

-- Check if column exists before adding
SET @dbname = DATABASE();
SET @tablename = 'Orders';
SET @columnname = 'payment_timestamp';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL AFTER paid_amount')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing orders: if payment_method is set, set payment_timestamp to order_date
UPDATE Orders 
SET payment_timestamp = order_date 
WHERE payment_method IS NOT NULL AND payment_method != '' AND payment_timestamp IS NULL;

