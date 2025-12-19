

my path
D:\xampp\htdocs\my_projects\mvclixo\src\public_html


i wanna have some that ate public i guess
--- thumbs
--- web

and i guess origonals are private?

my_project/
├── src/
│   └── ... (your PHP classes)
├── public_html/  <-- Your web server's document root
│   ├── index.php
│   ├── css/
│   └── store/
│       └── 22/        <--  Store ID
│       └── 41/
│       └── 312/
│       └── 555/
│           └── images/
│               ├── web/      <-- Public, optimized for web display
│               │    └── a1b2c3d4e5.jpg
│               │    └── a1b2c2qw7j.jpg
│               │    └── aqqw56y22e.jpg
│               └── thumbs/   <-- Public, small thumbnails
                │    └── a1b2c3d4e5.jpg
│               │    └── a1b2c2qw7j.jpg
│               │    └── aqqw56y22e.jpg
│
└── storage/      <-- NOT publicly accessible
│   └── store/
│       └── 22/        <--  Store ID
│       └── 41/
│       └── 312/
│       └── 555/
│           └── originals/
                │    └── a1b2c3d4e5.jpg
│               │    └── a1b2c2qw7j.jpg
│               │    └── aqqw56y22e.jpg

DATA BASE STRUCTURE for images


+----------+       +-------------------------+       +----------------+       +----------+
|          |       |                         |       |                |       |          |
| Products |------>|      Image Groups       |<------| Image Group    |<------|  Images  |
|          |       |         (ig)            |       | Relations (rel)|       |          |
+----------+       +-------------------------+       |                |       +----------+
            ``                                         +----------------+


## Image Table Design


| Column Name       | Data Type    | Nullable | Description
|-------------------|--------------|----------|--------------------------------------
1. Primary Key & Foreign Keys (for indexing and joins)
| id                | bigint       | NO       | Primary Key
| store_id          | int          | YES      | Store this record belongs to
| user_id           | int          | NO       | User who uploaded the image
|-------------------|--------------|----------|--------------------------------------
2. User Set Metadata
|-------------------|--------------|----------|--------------------------------------
| status            | varchar(20)  | NO       | active, pending, deleted
| title             | varchar(250) | YES      | Display name
| slug              | varchar(255) | YES      | URL-friendly identifier
| description       | text         | YES      | Longer image details
|-------------------|--------------|----------|--------------------------------------
3. File Identity & Storage
|-------------------|--------------|----------|--------------------------------------
| filename          | varchar(255) | NO       | Content hash (SHA-256) used as unique identifier
| original_filename | varchar(255) | YES      | Original filename at upload time
| mime_type         | varchar(100) | YES      | e.g., image/jpeg, image/webp
| file_size_bytes   | bigint       | YES      | Raw size of the file
| width             | int          | YES      | Original width in pixels
| height            | int          | YES      | Original height in pixels
| focal_point       | json         | YES      | X and Y coordinates for smart cropping (e.g., {"x": 0.5, "y": 0.3}).
| is_optimized      | tinyint(1)   | YES      | Flag indicating if original was optimized
| checksum          | varchar(255) | YES      | Checksum for integrity verification
|-------------------|--------------|----------|--------------------------------------
4. Accessibility & Legal
|-------------------|--------------|----------|--------------------------------------
| alt_text          | varchar(255) | YES      | Accessibility text
| license           | varchar(100) | YES      | Usage rights or license type
|-------------------|--------------|----------|--------------------------------------
5. Timestamps & Soft Delete
|-------------------|--------------|----------|--------------------------------------
| created_at        | datetime     | NO       | Record creation time
| updated_at        | datetime     | YES      | Last modified time
| deleted_at        | datetime     | YES      | Soft delete timestamp



An image file once assigned to a record can not be changed. "File Identity & Storage"  should not be updatable.
"User Set Metadata", and "Accessibility metadata" is updatable.

When uploading an image we need to make sure that image does not exist, meaning that ts has not been previously assigned
- Basically the filename/hash must be unique?

When an image record is deleted, the image file is also deleted.
Delete Cascade? in the future for image_rel table records Gallery_Image_Rel, Product_Image_Rel, ....._Image_Rel



# begin everthing beloa this line

- `ig` table- **Image Group table**
    - ig_id
    - name
    - description

- `image` table - **Image table**
    - image_id
    - name
    - description

- `ig_image_rel` table - **Image Group to Image Relationship table**
    - ig_id
    - image_id




- `gallery` table - **Gallery table**
    - gallery_id
    - ig_id - FK to `ig` table
    - name
    - description

- `product` table = **Product table**
    - product_id
    - ig_id - FK to `ig` table
    - name
    - description


- `category` table - **Category table**
    - category_id
    - ig_id - FK to `ig` table
    - name
    - description


y


<!--
  The browser is given a "menu" of options. It will automatically
  download the best one for the user's screen resolution.
-->
<img
    srcset="<?= $imageService->getUrl($image, 'thumbnail_2x') ?> 2x,
            <?= $imageService->getUrl($image, 'thumbnail') ?> 1x"
    src="<?= $imageService->getUrl($image, 'thumbnail') ?>"
    alt="A descriptive alt text"
>




### ImageProcessingService
- Responsibility: Solely focused on transforming image content at the pixel level (resizing, cropping, applying watermarks, changing format, optimizing). It doesn't know about file paths or database records.

### ImageStorageService
- Responsibility: Manages the physical image files on disk. This includes:
    - Saving uploaded files to their final location.
    - Calculating content hashes for filenames.
    - Retrieving file paths and URLs.
    - Deleting physical files and their generated presets.
    - It uses ImageProcessingService to create different versions (presets) of an image file.

### ImageService
- Responsibility: Manages the Image database records (entties) and the business logic surrounding them. This involves:
    - Interacting with ImageRepository for CRUD operations on the Image entity.
    - Orchestrating ImageStorageService when a new file needs to be associated with an Image record (e.g., uploading the raw file, getting its hash/metadata).
    - Enforcing business rules specific to the Image entity (e.g., "an image record, once created, cannot change its file," or checking for duplicate image content before creating a new database record).
    - Setting timestamps and other entity-level properties.
    - Its primary concern is the Image domain/feature's business logic. It coordinates actions between the database (via ImageRepository) and the file system (via ImageStorageService), but it doesn't do the low-level file operations itself.
