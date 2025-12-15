

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

image
- id
- store_id
- user_id
- title
- description
- filename
- original_filename: The name of the file from the user's computer (e.g., my-vacation-photo.png). You'll store this so you can show it to the user in their media library or use it when they download the file.
- mime_type: The type of the file, like image/jpeg or image/png. This is very important for telling the browser how to correctly display the image, especially when you create a script to let users download their originals.
- file_size_bytes: The size of the original uploaded file in bytes. This is useful for displaying to the user and for analytics.
- alt_text: This is text that describes the image. It's critical for website accessibility (for users with screen readers) and is also great for SEO. You'd let the user fill this in.
- created_at
- updated_at: Standard timestamp fields to track when the record was created and last modified.

Note: generate a completely new stored_filename whenever a user replaces an image. You should not reuse the old one.

+----------+       +-------------------------+       +----------------+       +----------+
|          |       |                         |       |                |       |          |
| Products |------>|      Image Groups       |<------| Image Group    |<------|  Images  |
|          |       |         (ig)            |       | Relations (rel)|       |          |
+----------+       +-------------------------+       |                |       +----------+
            ``                                         +----------------+


image table - actial image table
- image_id (int)
- store_id (int): Essential for your multi-tenant marketplace to keep images organized by store.
- user_id (int): Tracks which user uploaded the image, which is useful for permissions and logging.
- name
- description
- stored_filename: This is a security best practice. We never use the user's original filename on the server. Instead, we generate a random, unique name to prevent URL guessing and conflicts.
- original_filename: We store this so that when a user views their media library, they see the familiar name they originally uploaded.
- mime_type: Crucial for telling the browser how to correctly render the image.
- file_size_bytes: Useful for displaying file sizes to the user and for system analytics.
- alt_text: Very important for website accessibility (for screen readers) and for SEO.
- hash (string): A unique hash (like MD5 or SHA-256) of the image file's contents.
- width
- height (integer): Storing the dimensions (in pixels) of the original uploaded image. It allows you to calculate aspect ratios on the fly without having to read the image file
- focal_point (JSON or string): Stores the X and Y coordinates of the most important part of the image (e.g., {"x":0.5, "y":0.3}). This is for "smart cropping." When you generate thumbnails, instead of just cropping to the center, you can crop around the focal point (like a person's face), resulting in much better-looking thumbnails automatically. This is an advanced but very powerful feature.
- copyright or license(string)
- created_at
- updated_at: Standard timestamps to track when the image record was created and last modified.

ig_image_rel table - image group to image relationship table
- ig_id
- image_id



ig - image group table
- ig_id
- name
- description


gallery table
- gallery_id
- ig_id - FK to image group table
- name
- description

product table
- product_id
- ig_id - FK to image group table
- name
- description


- category table
- category_id
- ig_id - FK to image group table
- name
- description


but table design wise it is the same ?

ok...lets say i have a ig called Summer, that has 10 images of summer.
now i create a new gallery called my_summer
so when i attach the ig to is i insert a new gallery_image_group_relations
so i will need several features added to my diy framework?
image_group features - add/edit/view/del
- add add



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
