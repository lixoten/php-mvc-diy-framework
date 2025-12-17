# Image Presets and Dynamic Image Handling

This document explains how the framework manages and serves images using "presets" and how these are integrated with `ImageStorageService`, `ImageLinkFormatter`, and `filesystems.php` to support modern web practices like the `<picture>` element.

---

## 1. What are Image Presets?

Image "presets" are predefined configurations for image dimensions, quality, and intended usage (e.g., "thumbnail", "web-optimized", "original"). They allow the framework to generate and serve images in various sizes and formats to suit different contexts (e.g., a small thumbnail on a list page, a medium-sized image on a detail page, or the full original).

Using presets:
*   **Improves Performance:** By serving smaller, optimized images where appropriate.
*   **Enhances Responsiveness:** Allows browsers to pick the best image for the user's device and screen size.
*   **Ensures Consistency:** All "thumbs" images will adhere to the same dimensions and quality settings.

---

## 2. Image Storage Location and Structure

Images are stored in a multi-tenant file structure, ensuring separation per store and organizing by preset and format. Critically, **all physical image files on the disk will always include an extension** (e.g., `.jpg`, `.webp`, `.avif`).

*   **Original Images (Private Storage):** These are the untouched, full-resolution images, stored in a private location.
    *   **Path:** `d:\xampp\htdocs\my_projects\mvclixo\storage\store\{storeId}\originals\{hash}.{original_extension}`
    *   **Example:** `storage/store/555/originals/img_test1.jpg`

*   **Public Presets (Thumbnails, Web, etc.):** These are generated versions (resized, converted) of the original, stored in the public web directory. Each preset can have multiple formats (e.g., JPG, WebP, AVIF) for the same hash.
    *   **Path:** `d:\xampp\htdocs\my_projects\mvclixo\public_html\store\{storeId}\images\{preset}\{hash}.{format_extension}`
    *   **Examples for `img_test1` (hash) and `storeId=555`:**
        *   **Thumbnails (`thumbs` preset):**
            *   `public_html/store/555/images/thumbs/img_test1.jpg`
            *   `public_html/store/555/images/thumbs/img_test1.webp`
            *   `public_html/store/555/images/thumbs/img_test1.avif`
        *   **Web (`web` preset):**
            *   `public_html/store/555/images/web/img_test1.jpg`
            *   `public_html/store/555/images/web/img_test1.webp`
            *   `public_html/store/555/images/web/img_test1.avif`

---

## 3. `filesystems.php` (Configuration Hub)

This file (`src/Config/filesystems.php`) is the central place to define global image-related settings:

*   **`default_image_extension`**: Specifies the default file extension for each preset if one isn't explicitly provided or cannot be determined.
    *   Example: `'thumbs' => 'png'` means thumbnails will default to `.png`.
*   **`image_presets`**: Defines the standard characteristics for each preset.
    *   Example:
        ```php
        'thumbs' => [
            'width' => 150,
            'height' => 150,
            'quality' => 80,
        ]
        ```
*   **`image_generation.preferred_formats`**: An ordered list of image formats the application prefers to generate and serve. This order is crucial for how `<picture>` elements prioritize `<source>` tags.
    *   Example: `['avif', 'webp', 'jpg', 'png']`

---

## 4. `ImageStorageService` / `ImageStorageServiceInterface` (Image Logic)

This service (`src/Core/Services/ImageStorageService.php`) is responsible for all low-level image storage operations and path/URL resolution. It uses the `filesystems.php` configuration.

*   **`getUrl(hash, storeId, preset, ?extension)`**: Constructs the public-facing URL for a specific image, preset, and optional extension. It determines the final extension using configured defaults if `null` is passed.
*   **`getPath(hash, storeId, preset, ?extension)`**: Returns the absolute filesystem path for an image. It also determines the final extension and ensures directories are created when a file is to be written.
*   **`buildFilePath(hash, storeId, preset, ?extension)` (Private)**: A helper to construct paths *without* creating directories, used by `getUrls` for safe probing.
*   **`getUrls(hash, storeId, preset)`**: **This is key for `<picture>` tags.** It probes the filesystem, checking for each `preferred_format` (defined in `filesystems.php`) for the given hash and preset. It returns an associative array of `['extension' => 'public_url']` for all *existing* formats. Returns an empty array if no files are found.
*   **`upload(file, storeId)`**: (Currently a placeholder) In a full implementation, this method would not only save the original image but also generate all configured presets (e.g., `thumbs`, `web`) and convert them into the `preferred_formats` (e.g., `.jpg`, `.webp`, `.avif`), saving them to their respective public paths.
*   **`delete(hash, storeId, ?extension)`**: Deletes image files for a given hash across all presets and available formats.

---

## 5. `ImageLinkFormatter` (HTML Rendering)

The `ImageLinkFormatter` (`src/Core/Formatters/ImageLinkFormatter.php`) is responsible for turning an image hash (from a database record) into the appropriate HTML `<img>` or `<picture>` tag, ready for display in a list or detail view. It relies heavily on `ImageStorageService` and `filesystems.php`.

*   **`transform(value, options, record)`**:
    1.  **Parses Value:** Checks if the input `$value` contains an explicit extension (e.g., `myhash.png`).
    2.  **Determines Context:** Gets the `storeId` (from options or `CurrentContext`).
    3.  **Probes for Formats:**
        *   If an *explicit extension* was provided, it calls `ImageStorageService::getUrl()` for a single image.
        *   Otherwise, it calls `ImageStorageService::getUrls()` to discover *all available formats* for the specified `preset` (e.g., `thumbs`, `web`).
    4.  **Handles Fallbacks:** If no images are found (empty array from `getUrls`), it returns the `default_image` (configured in options).
    5.  **Renders HTML:**
        *   **Single Format (or explicit extension):** Renders a simple `<img>` tag.
        *   **Multiple Formats (from `getUrls`):** Renders a `<picture>` element.
            *   It iterates through `filesystems.image_generation.preferred_formats` to create `<source>` tags in the optimal order (e.g., `avif` first, then `webp`, then `jpg`).
            *   It includes a fallback `<img>` tag (typically pointing to the most compatible format like JPG or PNG) to ensure rendering on all browsers.
    6.  **Applies Options:** Integrates `width`, `height` (from options or `filesystems.image_presets`), `alt` text (from `alt_field` in the record or `alt_text` in options), and an optional `<a>` link wrapper.

---

## 6. Using the `<picture>` Element

The `<picture>` HTML element is a powerful tool for **responsive images** and **progressive enhancement**:

*   It contains multiple `<source>` elements and one `<img>` element.
*   The browser evaluates each `<source>` element in order.
*   It picks the **first `<source>` that matches its capabilities** (e.g., browser supports `AVIF` and the `type="image/avif"` source is present) and media queries.
*   If no `<source>` matches, it falls back to the `<img>` element.

This means users with modern browsers supporting AVIF will get the smallest/most efficient AVIF image, while older browsers or those not supporting AVIF will get WebP or JPG, ensuring broad compatibility with optimal performance.

---

This framework's image handling combines configuration, dedicated services, and modern HTML elements to provide a robust and flexible solution for managing and displaying images.