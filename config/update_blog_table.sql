-- Update blog_posts table to add missing columns
-- This fixes the fatal error in manage-blog.php

USE hotel_db;

-- Add categories and tags columns to blog_posts table
ALTER TABLE `blog_posts` 
ADD COLUMN `categories` TEXT NULL AFTER `image`,
ADD COLUMN `tags` TEXT NULL AFTER `categories`;

-- Verify the table structure
DESCRIBE blog_posts;
