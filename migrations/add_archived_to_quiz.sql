-- Migration to add archived column to quiz table
-- Date: 2025-10-16

-- Add archived column (0 = not archived, 1 = archived)
ALTER TABLE `quiz` 
ADD COLUMN `archived` TINYINT(1) NOT NULL DEFAULT 0 
AFTER `ip_check`;

-- Add index for better query performance
ALTER TABLE `quiz` 
ADD INDEX `idx_archived` (`archived`);

-- Update the structure file comment
-- To rollback: ALTER TABLE `quiz` DROP COLUMN `archived`;

