-- Room Gallery & Video Enhancement Database Changes
-- This file contains the SQL commands to enhance the rooms module with gallery and video functionality

-- 1. Add video_url column to existing rooms table
ALTER TABLE rooms ADD COLUMN video_url VARCHAR(255) NULL AFTER price_per_night;

-- 2. Create new room_gallery_images table for proper gallery management
-- Note: We'll keep the existing room_images table for backward compatibility
CREATE TABLE room_gallery_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    alt_text VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_room_gallery (room_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insert sample data for testing (optional)
-- INSERT INTO room_gallery_images (room_id, image_path, sort_order, alt_text) VALUES
-- (1, 'uploads/rooms/gallery/room1_gallery_1.jpg', 1, 'Room 1 Gallery Image 1'),
-- (1, 'uploads/rooms/gallery/room1_gallery_2.jpg', 2, 'Room 1 Gallery Image 2'),
-- (2, 'uploads/rooms/gallery/room2_gallery_1.jpg', 1, 'Room 2 Gallery Image 1');

-- 4. Update existing rooms with sample video URLs (optional)
-- UPDATE rooms SET video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' WHERE id = 1;
-- UPDATE rooms SET video_url = 'https://vimeo.com/123456789' WHERE id = 2;
