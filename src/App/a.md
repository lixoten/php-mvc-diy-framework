```SQL
CREATE TABLE posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for the post
    post_user_id INT NOT NULL,                         -- Reference to the user who created the post
    post_name VARCHAR(255) NOT NULL,        -- Human-readable name for the post
    post_status CHAR(1) NOT NULL DEFAULT 'P', -- Status of the v ('P' for Published, etc.)
    title VARCHAR(255) NOT NULL,                    -- Title of the post
    content TEXT NOT NULL,                          -- Main body content of the post
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp for when the post was created
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Timestamp for when the post  was last updated
    FOREIGN KEY (post_user_id) REFERENCES users(user_id) ON DELETE CASCADE -- Links the post to an author in the users table
);


CREATE TABLE galleries (
    gallery_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for the gallery
    gallery_name VARCHAR(255) NOT NULL,        -- Human-readable name for the gallery
    gallery_status CHAR(1) NOT NULL DEFAULT 'P', -- Status of the gallery ('P' for Published, etc.)
    type CHAR(1) NOT NULL DEFAULT 'I',    -- Type of gallery (single character, e.g., 'A', 'B', etc.)
    description TEXT,                  -- Optional description of the gallery
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp for when the gallery was created
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Timestamp for when the gallery was last updated
);

CREATE TABLE images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    image_status CHAR(1) NOT NULL DEFAULT 'P', -- Status of the image ('P' for Published, etc.)
    image_name VARCHAR(255) NOT NULL,        -- Human-readable name for the image
    filename VARCHAR(255) NOT NULL,            -- Just the image filename
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp for when the image was uploaded
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Timestamp for when the image was last updated
);
CREATE TABLE gallery_images (
    gallery_id INT NOT NULL,                 -- Foreign key to the galleries table
    image_id INT NOT NULL,                   -- Foreign key to the images table
    is_cover BOOLEAN DEFAULT FALSE,        -- Indicates whether this image is the cover for the gallery
    PRIMARY KEY (gallery_id, image_id),      -- Composite primary key (ensures uniqueness of gallery-image pairs)
    FOREIGN KEY (gallery_id) REFERENCES galleries(gallery_id) ON DELETE CASCADE,
    FOREIGN KEY (image_id) REFERENCES images(image_id) ON DELETE CASCADE
);








```