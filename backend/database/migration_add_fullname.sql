-- PROJECT ONE - MIGRATION: Add fullname column to users table
-- Run this SQL script if you already have the users table without fullname column

USE project_one_db;

-- Add fullname column to existing users table
ALTER TABLE users 
ADD COLUMN fullname VARCHAR(100) NULL AFTER id;

-- Verify the column was added
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = 'project_one_db' 
-- AND TABLE_NAME = 'users' 
-- AND COLUMN_NAME = 'fullname';

