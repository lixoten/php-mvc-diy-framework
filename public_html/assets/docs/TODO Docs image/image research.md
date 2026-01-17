**Model: Gemini 2.5 Flash Preview**

Here is the raw Markdown summary, with no extra surrounding lines or non-Markdown elements:

# Image Management & Upload Strategy (No JavaScript Baseline)

This document outlines the architectural and conceptual approach for handling image uploads, specifically addressing the challenges of a "no JavaScript" environment and ensuring adherence to SOLID principles.

## 1. Core Constraint: No JavaScript Baseline

The fundamental requirement is that the entire image upload and initial management workflow must function purely with HTML forms and server-side PHP. JavaScript will be introduced later as a progressive enhancement layer.

## 2. Multi-Step Form Approach for Single Image Upload

To overcome the notorious "lost file selection" issue (where browsers clear the file input field upon a form submission with validation errors, preventing pre-filling the input for security reasons), a multi-step process is implemented. This strategy focuses on processing a **single image per workflow**.

*   **Step 1: File Upload Only**
    *   **Purpose:** Exclusively handle the file input.
    *   **User Interface:** The user interacts with a form containing **only** the `<input type="file">` field and a submit button.
    *   **Server-Side Processing:**
        *   Validation focuses exclusively on the file itself (e.g., allowed file types, maximum size, actual image integrity via `getimagesize()` or `finfo_file()`).
        *   Upon successful validation, the uploaded file is moved from PHP's temporary upload location to a **secure, non-public, temporary storage directory** on the server's filesystem.
        *   A unique identifier, an `upload_token` (e.g., a UUID), is generated.
        *   A new record is created in the `image` database table with this `upload_token`, the temporary file's path, original filename, MIME type, size, and a `status` of `pending`.
    *   **Outcome:**
        *   **Success:** The user is then redirected to Step 2. The `upload_token` is stored in the user's session to link to the next step.
        *   **Failure:** If file validation fails, the user is redirected back to Step 1. They will need to re-select the file, and flash messages will display the specific validation errors.

*   **Step 2: Metadata Entry**
    *   **Purpose:** Collect all associated metadata for the uploaded image.
    *   **User Interface:** The user is presented with a **separate HTML form** that contains all the additional metadata fields (e.g., title, alt text, description, tags). This form **does not** contain a file input field.
    *   **Linking to Step 1:** The `upload_token` from Step 1 is securely retrieved from the user's session, linking the metadata to the already uploaded, pending image. A hidden input field within the form also carries this `upload_token` as a safeguard.
    *   **Image Preview:** It is highly recommended to display a preview of the temporarily uploaded image on this form to provide visual confirmation and context for metadata entry. This requires a **secure temporary image serving mechanism** (a dedicated route/controller) that reads the file from its non-public temporary path and serves it with correct HTTP headers.
    *   **Server-Side Processing:**
        *   Validation focuses on the metadata fields (e.g., required fields, length constraints).
        *   Upon successful metadata validation, the `image` record in the database is updated with all the submitted metadata.
        *   The image file is then moved from the temporary storage location to its **final, permanent storage location**.
        *   Finally, the `image` record's `status` in the database is changed from `pending` to `active`.
        *   The `current_image_upload_token` is cleared from the user's session.
    *   **Outcome:**
        *   **Success:** The user is redirected to the image's view page, an image listing, or a success message.
        *   **Failure:** If metadata validation fails, the user is redirected back to this same Step 2 form. Crucially, the previously uploaded file remains securely stored and associated. The form fields are pre-populated with previously submitted valid data, and flash messages display the specific metadata validation errors. The user only needs to correct the invalid fields without re-uploading the image.

## 3. Multi-Image Uploads (Future Progressive Enhancement)

While standard HTML allows selecting multiple files with the `multiple` attribute on an `<input type="file">`, implementing a robust, user-friendly multi-image upload experience *without JavaScript* is significantly complex. The primary challenges include:

*   The persistent "lost file selection" issue if any validation fails.
*   The difficulty of presenting and managing metadata forms for an unknown number of images in a pure server-side, redirect-based flow, potentially leading to very long and cumbersome forms.

**Recommendation:** The current multi-step single-image backend is designed to be a solid, secure, and testable foundation. When JavaScript is introduced in the future, the frontend can orchestrate the submission of *multiple single files* (or small batches) to the existing robust single-image backend API. This approach allows JavaScript to handle the complexities of the user experience (e.g., drag-and-drop, real-time previews, batch metadata entry), while the backend maintains clear, single-responsibility endpoints.

## 4. Secure Temporary File Storage

*   All uploaded files in Step 1 are moved to a non-public, server-side directory (e.g., temp_uploads) to prevent direct browser access.
*   Each file is assigned a **unique, randomized filename** (e.g., a UUID combined with the original file extension) when stored. This prevents naming collisions, even if a user uploads files with the same original name multiple times, and enhances security by obfuscating the actual filename. The `original_filename` is stored in the database.

## 5. Handling Abandoned Uploads

If a user initiates an upload (completes Step 1) but abandons the process before completing Step 2 (e.g., closes the browser, navigates away, loses network connection, or simply decides not to continue), the temporary file and its `pending` database record would remain on the server.

*   **Automated Cleanup (Cron Job):** A critical component is an **automated, scheduled task** (like a cron job on Linux or a Scheduled Task on Windows).
    *   This task executes a dedicated console command within the framework (e.g., `image:cleanup-temp`).
    *   This console command, in turn, utilizes a reusable **`ImageCleanupService`**.
    *   The `ImageCleanupService` identifies and removes all `pending` `image` records from the database that are older than a configured threshold (e.g., 1 hour, 24 hours) based on their `created_at` timestamp.
    *   For each identified record, it deletes the corresponding temporary image file from the secure temporary storage directory.
    *   This ensures system hygiene, prevents the accumulation of orphaned files, and maintains database consistency. This automated process should **not** be triggered by user actions on the frontend.
*   **User Experience (if returning later):** If a user attempts to resume an abandoned upload after the cleanup threshold, the application will not find the associated `pending` record or temporary file. The user will be redirected back to Step 1 with a message indicating that their previous upload session has expired or was not found, requiring them to restart the process.

## 6. User-Initiated Cancellation and Changes

In Step 2 (Metadata Entry), users are provided with explicit options to manage their upload, enhancing the user experience:

*   **"Cancel Upload":**
    *   **User Intent:** The user explicitly wishes to discard the current in-progress upload and abandon this specific image. This is available **after Step 1** is completed and a file is pending.
    *   **System Action:** When this option is chosen, the `ImageController` receives the request. It then invokes the `ImageCleanupService` (reusing its logic) to perform an immediate cleanup:
        *   Locate the specific `pending` image record in the database using its `upload_token`.
        *   Delete the associated temporary image file from storage.
        *   Remove the `pending` record from the database.
        *   The user's session `current_image_upload_token` is cleared.
    *   **Redirection:** The user is redirected to a neutral, safe page (e.g., an image listing, dashboard, or home page) with a flash message confirming the cancellation.

*   **"Upload Different Image":**
    *   **User Intent:** The user has decided that the currently pending image is not the one they wish to proceed with. They want to discard it and begin the upload process again with a new file. This is available **after Step 1** is completed and a file is pending.
    *   **System Action:** Similar to "Cancel," the `ImageController` invokes the `ImageCleanupService` to perform an immediate cleanup of the *current* pending image (file and database record).
    *   The user's session `current_image_upload_token` is cleared.
    *   **Redirection:** The user is redirected back to **Step 1 (the initial file upload form)**, typically with a flash message indicating they can now select a new image.

**Relationship to Abandonment:** Both "Cancel Upload" and "Upload Different Image" actions result in the immediate and explicit abandonment of the currently pending image. The `ImageCleanupService` is a reusable component that encapsulates the cleanup logic, serving both the automated scheduled task (via a console command) and these user-initiated actions (via the web controller). This provides a clean, controlled way for users to manage their choices without leaving orphaned data on the server.













Phase 1 would focus on establishing the core `ImageCleanupService` and integrating it with user actions, even before Step 2 (metadata entry) is fully developed.

Here's how Phase 1 would look, allowing users to cancel or re-upload a different image right after Step 1:

**Step-by-step plan for Phase 1:**

1.  **Define `ImageCleanupServiceInterface`:** Create an interface for the `ImageCleanupService` to adhere to DIP.
2.  **Implement `ImageCleanupService`:** Develop the concrete service that encapsulates the logic for deleting temporary files and associated database records. This service will be reusable.
3.  **Modify `ImageController` (Step 1 handler):**
    *   Add methods to handle "Cancel Upload" and "Upload Different Image" requests.
    *   Inject `ImageCleanupServiceInterface` into the controller.
    *   These new actions will call the `cleanup` method of the `ImageCleanupService`, passing the `upload_token` retrieved from the session.
    *   Redirect the user appropriately after cleanup.
4.  **Update `dependencies.php`:** Register the `ImageCleanupService` for dependency injection.
5.  **Modify Step 1 View:** Add the "Cancel Upload" and "Upload Different Image" buttons, linking them to the new controller actions.
6.  **Create `image` table (if not already existing):** Ensure the database table for images exists with the necessary columns (`upload_token`, `temp_path`, `original_filename`, `status`, `created_at`).

### [image research.md](file:///d%3A/xampp/htdocs/my_projects/mvclixo/public_html/assets/docs/TODO%20Docs%20image/image%20research.md)

Update the "User-Initiated Cancellation and Changes" section to reflect the phased approach, specifically clarifying that these actions can be implemented even before Step 2 is fully operational, operating on the pending image after Step 1.

````markdown
...existing code...

## 6. User-Initiated Cancellation and Changes

In Step 2 (Metadata Entry), users are provided with explicit options to manage their upload, enhancing the user experience:
**Phase 1 Note**: These user-initiated actions (`Cancel Upload` and `Upload Different Image`) can be implemented and made functional immediately after Step 1 (File Upload Only) is complete, even before Step 2 (Metadata Entry) is fully developed. When invoked, they will clean up the `pending` image associated with the current session.

*   **"Cancel Upload":**
    *   **User Intent:** The user explicitly wishes to discard the current in-progress upload and abandon this specific image. This is available **after Step 1** is completed and a file is pending.
    *   **System Action:** When this option is chosen, the `ImageController` receives the request. It then invokes the `ImageCleanupService` (reusing its logic) to perform an immediate cleanup:
        *   Locate the specific `pending` image record in the database using its `upload_token`.
        *   Delete the associated temporary image file from storage.
        *   Remove the `pending` record from the database.
        *   The user's session `current_image_upload_token` is cleared.
    *   **Redirection:** The user is redirected to a neutral, safe page (e.g., an image listing, dashboard, or home page) with a flash message confirming the cancellation.

*   **"Upload Different Image":**
    *   **User Intent:** The user has decided that the currently pending image is not the one they wish to proceed with. They want to discard it and begin the upload process again with a new file. This is available **after Step 1** is completed and a file is pending.
    *   **System Action:** Similar to "Cancel," the `ImageController` invokes the `ImageCleanupService` to perform an immediate cleanup of the *current* pending image (file and database record).
    *   The user's session `current_image_upload_token` is cleared.
    *   **Redirection:** The user is redirected back to **Step 1 (the initial file upload form)**, typically with a flash message indicating they can now select a new image.

...existing code...
````

Made changes.




//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
**I am using Claude Sonnet 4.5**

---

## 📋 **Implementation Phases Document**

Based on your research document and codebase analysis, here's a structured phased approach:

---

# 🚀 **Image Upload Feature - Phased Implementation Plan**

## **Overview**

This document outlines a **phased, iterative approach** to implementing a robust, SOLID-compliant image upload system that works **without JavaScript** as a baseline, with progressive enhancement planned for later phases.

---

## **Phase 0: Pre-Implementation Checklist** ✅

Before starting Phase 1, verify these prerequisites:

### **✅ Database Schema**
- [ ] `image` table exists with required columns
- [ ] `upload_token` column added (VARCHAR(36), UNIQUE, NULLABLE)
- [ ] `temp_path` column added (VARCHAR(500), NULLABLE)
- [ ] `client_mime_type` column added (VARCHAR(50), NULLABLE)
- [ ] `status` column supports values: `P` (Pending), `A` (Active), `I` (Inactive)
- [ ] Index on `upload_token` created

### **✅ Core Services**
- [ ] `TemporaryFileUploadService` exists and tested
- [ ] `ImageStorageServiceInterface` defined
- [ ] `ImageStorageService` basic implementation exists
- [ ] `FieldRegistryService` configured for image fields

### **✅ Configuration**
- [ ] `temp_uploads/` directory created (non-public, writable)
- [ ] `src/App/Features/Image/Config/image_fields.php` defined
- [ ] Routes registered in router for image feature

---

## **Phase 1: Single Image Upload - Step 1 (File Upload Only)** 🎯

**Goal:** User can upload a single image file, and it's stored in temporary storage with a `pending` status.

### **Deliverables:**

#### **1.1 Database Preparation**
- ✅ Run migration to add `upload_token`, `temp_path`, `client_mime_type` columns
- ✅ Verify `status` column supports `P` (Pending) value

#### **1.2 Service Layer**
- ✅ **`ImageCleanupServiceInterface`** (new)
  - Define `cleanupSinglePendingImage(string $uploadToken): void`
  - Define `cleanupExpiredPendingUploads(): int`

- ✅ **`ImageCleanupService`** (new)
  - Implement cleanup logic for temporary files + database records
  - Inject `ImageRepositoryInterface`, `LoggerInterface`

- ✅ **`ImageService::createPendingImageRecord()`** (new method)
  - Accept `$tempFileMetadata`, `$userId`, `$storeId`
  - Generate `upload_token` (UUID)
  - Create `pending` image record in database
  - Return `['upload_token' => '...', 'image_id' => 123]`

#### **1.3 Repository Layer**
- ✅ **`ImageRepositoryInterface::findByUploadToken()`** (new method)
  - Signature: `public function findByUploadToken(string $uploadToken): ?Image;`

- ✅ **`ImageRepository::findByUploadToken()`** (implementation)
  - Query database by `upload_token`
  - Return `Image` entity or `null`

#### **1.4 Controller Layer**
- ✅ **`ImageController::uploadStepOneAction()`** (new)
  - Handle GET: Render Step 1 form (file upload only)
  - Handle POST:
    - Process uploaded file via `TemporaryFileUploadService`
    - Call `ImageService::createPendingImageRecord()`
    - Store `upload_token` in session
    - Redirect to Step 2 placeholder (or success message)

- ✅ **`ImageController::cancelUploadAction()`** (new)
  - Retrieve `upload_token` from session
  - Call `ImageCleanupService::cleanupSinglePendingImage()`
  - Clear session token
  - Redirect to safe page with flash message

- ✅ **`ImageController::uploadDifferentImageAction()`** (new)
  - Same as `cancelUploadAction()` but redirect to Step 1 form

#### **1.5 View Layer**
- ✅ **`upload_step_one.php`** (new view)
  - Single `<input type="file" name="image_file" required>`
  - Submit button: "Upload Image"
  - Display flash messages for validation errors

- ✅ **Update existing image list view** (optional)
  - Add "Upload New Image" button linking to Step 1

#### **1.6 Routing**
```php
// src/routes/web.php (or wherever routes are defined)
$router->get('/image/upload', [ImageController::class, 'uploadStepOneAction']);
$router->post('/image/upload', [ImageController::class, 'uploadStepOneAction']);
$router->post('/image/cancel', [ImageController::class, 'cancelUploadAction']);
$router->post('/image/upload-different', [ImageController::class, 'uploadDifferentImageAction']);
```

#### **1.7 Dependency Injection**
```php
// src/dependencies.php
\App\Features\Image\ImageCleanupServiceInterface::class => \DI\autowire(\App\Features\Image\ImageCleanupService::class),
```

---

### **Phase 1 Testing Checklist:**
- [ ] User can select and upload an image file
- [ ] File is moved to `temp_uploads/` with unique filename
- [ ] Database record created with `status='P'` and `upload_token`
- [ ] Session stores `current_image_upload_token`
- [ ] "Cancel Upload" deletes temp file and database record
- [ ] "Upload Different Image" redirects to Step 1 form
- [ ] Invalid file types/sizes show validation errors

---

## **Phase 2: Single Image Upload - Step 2 (Metadata Entry)** 📝

**Goal:** User can provide metadata for uploaded image, finalizing the upload.

### **Deliverables:**

#### **2.1 Service Layer**
- ✅ **`ImageService::finalizeImageRecord()`** (new method)
  - Accept `$uploadToken`, `$metadataFormData`, `$currentStoreId`
  - Validate `upload_token` against session
  - Verify image is `pending` and belongs to correct store
  - Move file from temp to permanent storage via `ImageStorageService::uploadFromTemporary()`
  - Update image record with metadata + change `status='A'`
  - Clear `temp_path` and `upload_token` from database
  - Clear session token

#### **2.2 Controller Layer**
- ✅ **`ImageController::uploadStepTwoAction()`** (new)
  - Handle GET: Render Step 2 form (metadata fields)
    - Display image preview from temporary storage
    - Validate `upload_token` from session
  - Handle POST:
    - Process metadata form
    - Call `ImageService::finalizeImageRecord()`
    - Redirect to image view page

- ✅ **`ImageController::serveTempImageAction()`** (new, security-critical)
  - Verify `upload_token` matches session
  - Serve temporary image file with proper HTTP headers
  - **Never expose direct filesystem paths to browser**

#### **2.3 View Layer**
- ✅ **`upload_step_two.php`** (new view)
  - Hidden input: `<input type="hidden" name="upload_token" value="...">`
  - Image preview: `<img src="/image/temp-preview?token=...">`
  - Metadata fields: `title`, `alt_text`, `description`, `slug`
  - Submit button: "Complete Upload"
  - "Cancel Upload" button (POST to `/image/cancel`)
  - "Upload Different Image" button (POST to `/image/upload-different`)

#### **2.4 Routing**
```php
$router->get('/image/upload/metadata', [ImageController::class, 'uploadStepTwoAction']);
$router->post('/image/upload/metadata', [ImageController::class, 'uploadStepTwoAction']);
$router->get('/image/temp-preview', [ImageController::class, 'serveTempImageAction']); // Security: validate token
```

---

### **Phase 2 Testing Checklist:**
- [ ] After Step 1, user redirected to Step 2 form
- [ ] Image preview displays correctly from temporary storage
- [ ] Metadata form validation works (required fields, length limits)
- [ ] On successful submission, image moved to permanent storage
- [ ] Database record updated with metadata and `status='A'`
- [ ] User redirected to image view page
- [ ] Session token cleared after finalization
- [ ] "Cancel" and "Upload Different" still work from Step 2

---

## **Phase 3: Automated Cleanup (Cron Job)** 🧹

**Goal:** Automatically delete abandoned pending uploads after expiry threshold.

### **Deliverables:**

#### **3.1 Console Command**
- ✅ **`ImageCleanupCommand`** (new)
  - Path: `src/Core/Console/Commands/ImageCleanupCommand.php`
  - Inject `ImageCleanupService`
  - Execute `cleanupExpiredPendingUploads()`
  - Log results

#### **3.2 Configuration**
```php
// src/Config/app.php (or similar)
'image' => [
    'temp_upload_expiry_minutes' => 60, // 1 hour default
],
```

#### **3.3 Cron Job Setup**
```bash
# Linux/macOS crontab
# Run every hour at minute 0
0 * * * * cd /path/to/mvclixo && php bin/console.php image:cleanup >> logs/cleanup.log 2>&1

# Windows Task Scheduler (create .bat file)
cd /d D:\xampp\htdocs\my_projects\mvclixo
php bin\console.php image:cleanup >> logs\cleanup.log 2>&1
```

#### **3.4 Logging**
- ✅ Log each cleanup run (timestamp, count of deleted records)
- ✅ Log errors if temp file deletion fails

---

### **Phase 3 Testing Checklist:**
- [ ] Create test pending image record with `created_at` > 1 hour ago
- [ ] Run `php console.php image:cleanup`
- [ ] Verify temp file deleted from filesystem
- [ ] Verify database record deleted
- [ ] Verify recent pending uploads NOT deleted
- [ ] Verify cron job runs automatically

---

## **Phase 4: Multi-Image Upload (JavaScript Enhancement)** 🚀

**Goal:** Allow users to upload multiple images in one session with improved UX.

### **Deliverables:**

#### **4.1 Frontend JavaScript**
- ✅ Drag-and-drop file selection
- ✅ Client-side file validation (type, size)
- ✅ Progress bars for uploads
- ✅ Batch metadata entry interface
- ✅ **Backend API:** Reuse existing `uploadStepOneAction()` endpoint
  - JS sends multiple AJAX requests to single-image endpoint
  - Server responds with JSON instead of redirects

#### **4.2 Controller Enhancements**
- ✅ **`ImageController::uploadStepOneAction()`** modifications:
  - Detect AJAX request (`X-Requested-With: XMLHttpRequest`)
  - Return JSON response: `{'success': true, 'upload_token': '...', 'image_id': 123}`
  - Keep existing HTML redirect for non-JS fallback

#### **4.3 View Layer**
- ✅ Progressive enhancement: Keep existing HTML forms as fallback
- ✅ JavaScript-enhanced interface: Hidden by default, shown if JS enabled

---

### **Phase 4 Testing Checklist:**
- [ ] Non-JS users still use Step 1 → Step 2 flow
- [ ] JS-enabled users see enhanced drag-and-drop interface
- [ ] Multiple files can be selected and uploaded concurrently
- [ ] Progress indicators update correctly
- [ ] Backend API returns JSON for AJAX requests
- [ ] Backend returns HTML redirects for non-AJAX requests

---

## **Phase 5: Advanced Features** 🎨

**Goal:** Add image manipulation, gallery management, and optimization.

### **Deliverables:**

#### **5.1 Image Presets**
- ✅ Automatic generation of thumbnails (`thumbs/`, `web/`)
- ✅ Configure preset sizes in `src/Config/image_presets.php`
- ✅ **`ImageStorageService::uploadFromTemporary()`** generates presets automatically

#### **5.2 Gallery Management**
- ✅ Associate images with galleries via `gallery_id` foreign key
- ✅ Reorder images within gallery (`display_order`)
- ✅ Mark images as featured (`is_featured`)

#### **5.3 Image Optimization**
- ✅ WebP conversion for supported browsers
- ✅ Lossy/lossless compression options
- ✅ EXIF data stripping for privacy

#### **5.4 Bulk Operations**
- ✅ Batch delete
- ✅ Batch status change (`A` → `I`)
- ✅ Batch gallery assignment

---

## **Current Status Assessment**

Based on your codebase:

| Component | Status | Action Required |
|-----------|--------|-----------------|
| `TemporaryFileUploadService` | ✅ **Complete** | None (already SOLID) |
| `ImageStorageService` | ⚠️ **Needs Refactor** | Implement `uploadFromTemporary()` fully |
| `ImageService` | ⚠️ **Needs Split** | Extract to `createPendingImageRecord()` + `finalizeImageRecord()` |
| `Image` entity | ⚠️ **Needs Columns** | Add `upload_token`, `temp_path`, `client_mime_type` |
| `ImageController` | ❌ **Needs New Actions** | Add `uploadStepOneAction()`, `uploadStepTwoAction()`, etc. |
| `ImageCleanupService` | ❌ **Does Not Exist** | Create new service |
| Database schema | ⚠️ **Needs Migration** | Add new columns |

---

## **Recommended Starting Point**

### **✅ Start with Phase 1 - Step 1 Implementation**

Let's begin with the **smallest, testable increment**:

1. ✅ Create database migration for new columns
2. ✅ Implement `ImageCleanupService` (small, reusable)
3. ✅ Add `ImageService::createPendingImageRecord()` method
4. ✅ Create `ImageController::uploadStepOneAction()`
5. ✅ Build `upload_step_one.php` view
6. ✅ Test file upload → pending record creation

---

## 🚀 **Ready to Start?**

Shall I generate:

1. **Database migration file** for Phase 1?
2. **`ImageCleanupService.php`** implementation?
3. **`ImageController` new actions** for Step 1?
4. **`upload_step_one.php` view** template?

Which would you like to tackle first? 🛠️

┌─────────────────────────────────────────────────────────────┐
│ AUTOMATED CLEANUP (Cron Job)                                │
│ ✅ ImageCleanupService::cleanupExpiredPendingUploads()      │
│    └─> Queries DB for expired records                       │
│    └─> Loops through results                                │
│    └─> Deletes temp files + DB records                      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ USER-INITIATED ACTIONS (Controller)                         │
│ ✅ ImageService::cancelPendingUpload(string $uploadToken)   │
│    └─> Validates token belongs to current user/session      │
│    └─> Calls ImageCleanupService::cleanupSinglePending()    │
│    └─> Clears session token                                 │
│    └─> Returns result (success/failure)                     │
└─────────────────────────────────────────────────────────────┘

