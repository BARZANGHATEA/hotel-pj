-- Update blog tables to ensure all required columns exist
-- This script is idempotent and can be run multiple times safely

USE hotel_db;

-- Add updated_at column to blog_posts if it doesn't exist
ALTER TABLE `blog_posts` 
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL AFTER `created_at`;

-- Add updated_at column to blog_post_translations if it doesn't exist
ALTER TABLE `blog_post_translations` 
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL AFTER `summary`;

-- Ensure status column has correct default value for existing posts
UPDATE `blog_posts` SET `status` = 'draft' WHERE `status` IS NULL OR `status` = '';

-- Verify the table structures
DESCRIBE blog_posts;
DESCRIBE blog_post_translations;

-- Show current blog posts with their status
SELECT id, image, status, created_at, updated_at FROM blog_posts ORDER BY id;
