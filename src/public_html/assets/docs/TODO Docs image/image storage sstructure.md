# 💾 Image Storage and File Structure for MVCLixo Framework

This document summarizes the secure and performant file structure strategy developed for your custom PHP framework, ensuring separation between private, high-resolution original files and public, optimized display files.

---

## 1. 📂 Core Framework Path Structure

Based on your framework location (`D:\xampp\htdocs\my_projects\mvclixo\src`), we define two primary areas: the **Web Root** (publicly accessible) and the **Application Root** (private, secure storage).

| Directory | Purpose | Access |
| :--- | :--- | :--- |
| **`src/public_html`** | The **Web Root**. Everything here is accessible via a URL. | **Public** |
| **`src/storage`** | The **Application Root**. Files here are protected and secure. | **Private** |

---

## 2. 🔒 Protected Storage: The Master Assets

Original, high-resolution files **must** be kept outside the web root for security. This prevents direct unauthorized browser access and protects the full-size, unoptimized file.

### 2.1. Originals Directory

| Location (Absolute Path) | Role | Contents |
| :--- | :--- | :--- |
| `src/storage/originals/` | **Master Asset Vault** | The original file exactly as uploaded by the user. |

### 2.2. Directory Structure Detail

To manage multiple stores/users, the path should be organized by the `[STORE_ID]`:

`src/storage/originals/[STORE_ID]/[UNIQUE_FILENAME].jpg`

---

## 3. 🚀 Public Storage: The Display Assets

These files are the resized and optimized versions. They are saved within the web root so the web server (XAMPP/Apache) can serve them directly, achieving the fastest loading speeds.

### 3.1. Display Directories

We separate files into directories based on their **Image Preset** (size).

| Location (Relative to `public_html`) | Preset Name | Purpose |
| :--- | :--- | :--- |
| `public_html/images/[STORE_ID]/web/` | **Web Optimized** | Primary display size (e.g., 1200px max width). Used in the main content area. |
| `public_html/images/[STORE_ID]/thumbs/` | **Thumbnail** | Smallest display size (e.g., 150x150px square). Used in grids and listings. |

### 3.2. Serving via URL

The file path translates directly to a URL:

* **File Path:** `src/public_html/images/1/web/42_unique_name.jpg`
* **URL Example:** `http://localhost/my_projects/mvclixo/images/1/web/42_unique_name.jpg`

---

## 4. 🔄 Image Generation and Serving Strategy

The system uses a **Hybrid Approach** for performance and flexibility.

### 4.1. Static Generation (Initial Upload)

When a user uploads an image:

1.  The high-res file is saved securely to `src/storage/originals/`.
2.  The PHP application processes it (resizes, applies watermark).
3.  The derivatives (Web, Thumbs) are saved to the **Public Storage** (`src/public_html/images/...`).
4.  The HTML then uses the **`<img srcset>`** attribute to tell the browser which of these static files to download.

### 4.2. Hybrid Regeneration (On Demand)

If a static derivative is deleted (e.g., to apply a new watermark), the system handles the missing file gracefully:

1.  **Request:** A user requests a file that is now missing (404 error).
2.  **Redirect:** The web server redirects the 404 error to a custom PHP script.
3.  **Process:** The script reads the original from the **Protected Storage**, applies the new settings, and **recreates the static file** in the public folder.
4.  **Serve:** The new static file is served to the user and remains permanently in the public folder for all future requests.

---

## 5. 🔑 Key Database Fields for File Management

Your `images` table must store the critical metadata needed to locate and serve these files:

| Column Name | Purpose | Example Value |
| :--- | :--- | :--- |
| `store_id` | Links the file to its owner's storage directory. | `1` |
| `filename_unique` | The unique name of the original file. | `a4b6c8d9-original.jpg` |
| `filename_web` | The unique name of the web-optimized file. | `42_web_optimized.jpg` |
| `filename_thumb` | The unique name of the thumbnail file. | `42_thumb.jpg` |











////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////



# 💾 DIY PHP Framework: Image and Database Structure

This document details the recommended database schema for high reuse and the strategy for storing and serving images efficiently.

---

## 🏛️ Database Schema: The Reusable Image Group Model

This structure uses a centralized **Image Group** hub (`image_groups` and `ig_images_rel`) to simplify relationships for **Products**, **Galleries**, and **Categories**.

### 1. Core Entities (The Main Objects)

These tables define the fundamental objects in your multi-store system.

| Table Name | Role | Key Fields |
| :--- | :--- | :--- |
| **`stores`** | Defines the owner of the data/images. | `id` (PK), `name` |
| **`images`** | The single master list of all image assets. | `id` (PK), **`store_id` (FK)**, `filename_original` |
| **`products`** | Transactional item data. | `id` (PK), `name`, `price` |
| **`galleries`** | Curated image showcase data. | `id` (PK), `title`, `description` |
| **`categories`** | Metadata/Tags for filtering. | `id` (PK), `name` |

### 2. The Reusable Relationship Tables (The Group Hub)

These tables handle all image collection logic and facilitate sharing across entities.

#### `image_groups` (The Group Container)

This table defines the specific collection for a Product, Gallery, or Category.

| Column Name | Data Type | Key | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `INT` | PK | Unique ID for this collection (Group ID). |
| **`owner_type`** | `VARCHAR(50)` | Composite | Specifies the parent table ('Product', 'Gallery', or 'Category'). |
| **`owner_id`** | `INT` | Composite | The ID of the specific parent entity (e.g., Product ID 5). |

#### `ig_images_rel` (Image Group Relationship)

This is the Many-to-Many join that links images to their groups and defines their order.

| Column Name | Data Type | Key | Purpose |
| :--- | :--- | :--- | :--- |
| **`group_id`** | `INT` | FK, Composite | Links to **`image_groups.id`**. |
| **`image_id`** | `INT` | FK, Composite | Links to **`images.id`**. |
| `display_order` | `INT` | | Defines the sequence within the group (Crucial for Products/Galleries). |

---

## 📁 Image Storage Strategy

Images are separated into **Protected** (Master Asset) and **Public** (Display Assets) folders, organized by the `[STORE_ID]`.

### 1. 🔒 Protected Storage (The Master Asset)

This directory is **blocked from direct web access** and holds the high-resolution files.

| File Type | Purpose | Directory Path Structure |
| :--- | :--- | :--- |
| **Original** | Untouched, high-resolution master file. | `storage/originals/[STORE_ID]/` |

### 2. 🚀 Public Storage (The Display Assets)

This directory **must be publicly accessible** via a URL.

| File Type | Purpose | Directory Path Structure |
| :--- | :--- | :--- |
| **Web-Optimized** | Primary display size (e.g., 1200px max width). | `public/images/[STORE_ID]/web/` |
| **Thumbnail** | Smallest display size (e.g., 150x150px square). | `public/images/[STORE_ID]/thumbs/` |

---

## 🖼️ Image Serving Approaches

### 1. Static Image Serving (The Default/Simple Model)

* **Process:** Fixed sizes are **pre-generated** once at upload and saved permanently to `/web/` and `/thumbs/`.
* **Serving:** The HTML uses `<img srcset>` to list all pre-made files. The browser selects the best fit for its screen and downloads the static file directly.
* **Drawback:** Changing an attribute (e.g., a watermark) requires **manual deletion and regeneration** of all derivative files.

### 2. Dynamic Image Serving (The Advanced Model)

* **Process:** The image is only resized and processed **on the first request** based on URL parameters (`?width=X`).
* **Serving:** The resulting file is saved to a **cache folder**. Subsequent requests for that exact size are served from the cache.

### 3. The Hybrid Approach (Recommended for Growth)

This approach uses **on-demand generation** where the public folders (`/web/`, `/thumbs/`) act as a **Permanent Cache**.

* **Regeneration:** When an attribute is updated (e.g., watermark changed), you **delete the old derivatives** from the public folders.
* **Self-Correction:** If a browser requests a file that is now missing (404 error), the web server redirects the request to a PHP script.
* **Logic:** The script reads the original, applies the new watermark, recreates the missing file, saves it back into the `/web/` or `/thumbs/` folder, and serves it.////////
*



=======================================
=======================================
=======================================
=======================================
=======================================
=======================================
=======================================
=======================================
=======================================
# 🖼️ Understanding the `<img srcset>` Attribute

The `srcset` (Source Set) attribute is a core HTML technique used for **responsive images**. Its primary function is to allow the browser to select and download the most appropriately sized image file for the user's specific device, screen resolution, and network conditions.

The goal is to serve the **smallest file size necessary** to achieve the best visual quality, leading to faster page load times and reduced bandwidth consumption.

---

## 💻 `srcset` Syntax Breakdown

The `srcset` attribute provides a "menu" of available image sources, separated by commas. Each source consists of two parts: the **URL of the image file** and its **width descriptor** (the actual intrinsic width of that file).

### Example Code

```html
<img
    srcset="
        /public/images/42/thumbs/unique_name.jpg 300w,
        /public/images/42/medium/unique_name.jpg 800w,
        /public/images/42/web/unique_name.jpg 1200w"
    sizes="(max-width: 600px) 300px,
           (max-width: 1200px) 800px,
           1200px"
    src="/public/images/42/web/unique_name.jpg"
    alt="Responsive image example">
```