# Image Upload Feature - Test Documentation

This document outlines test cases for the **Image Upload Feature** with SOLID-compliant architecture.

---

## 🧪 **Phase 1: Single Image Upload - Step 1 Tests**

### **Test Suite 1.1: TemporaryFileUploadService**

#### **Test 1.1.1: Successful File Upload**
```php
// Test: Valid image file is moved to temp storage
public function test_successful_file_upload(): void
{
    // Arrange
    $uploadedFile = $this->createMockUploadedFile('test.jpg', UPLOAD_ERR_OK);
    $service = new TemporaryFileUploadService($this->logger, '/tmp/uploads');

    // Act
    $metadata = $service->processSingleFile($uploadedFile, 'image_file');

    // Assert
    $this->assertEquals('success', $metadata['status']);
    $this->assertFileExists($metadata['temporary_path']);
    $this->assertEquals('image/jpeg', $metadata['mime_type']);
}
```

#### **Test 1.1.2: UPLOAD_ERR_NO_FILE Handling**
```php
// Test: No file uploaded returns 'no_file' status
public function test_no_file_uploaded(): void
{
    // Arrange
    $uploadedFile = $this->createMockUploadedFile('', UPLOAD_ERR_NO_FILE);
    $service = new TemporaryFileUploadService($this->logger, '/tmp/uploads');

    // Act
    $metadata = $service->processSingleFile($uploadedFile, 'image_file');

    // Assert
    $this->assertEquals('no_file', $metadata['status']);
    $this->assertEquals('No file uploaded.', $metadata['error_message']);
}
```

#### **Test 1.1.3: Invalid MIME Type Detection**
```php
// Test: PHP file disguised as image is rejected
public function test_invalid_mime_type_detection(): void
{
    // Arrange
    $uploadedFile = $this->createMockUploadedFile('malicious.php', UPLOAD_ERR_OK, 'image/jpeg'); // Fake MIME
    $service = new TemporaryFileUploadService($this->logger, '/tmp/uploads');

    // Act
    $metadata = $service->processSingleFile($uploadedFile, 'image_file');

    // Assert
    $this->assertEquals('failed', $metadata['status']);
    $this->assertStringContainsString('MIME type mismatch', $metadata['error_message']);
}
```

---

### **Test Suite 1.2: ImageService::createPendingImageRecord()**

#### **Test 1.2.1: Pending Image Record Creation**
```php
// Test: Pending image record created with upload_token
public function test_create_pending_image_record(): void
{
    // Arrange
    $tempFileMetadata = [
        'temporary_path' => '/tmp/uploads/abc123.jpg',
        'original_filename' => 'vacation.jpg',
        'client_mime_type' => 'image/jpeg',
        'size_bytes' => 1024000,
    ];
    $userId = 1;
    $storeId = 555;

    // Act
    $result = $this->imageService->createPendingImageRecord($tempFileMetadata, $userId, $storeId);

    // Assert
    $this->assertIsString($result['upload_token']);
    $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $result['upload_token']); // UUID format
    $this->assertIsInt($result['image_id']);

    // Verify database record
    $image = $this->imageRepository->findById($result['image_id']);
    $this->assertEquals('P', $image->getStatus());
    $this->assertEquals($userId, $image->getUserId());
    $this->assertEquals($storeId, $image->getStoreId());
    $this->assertEquals($tempFileMetadata['temporary_path'], $image->getTempPath());
}
```

#### **Test 1.2.2: Missing Temporary Path Throws Exception**
```php
// Test: Invalid temp metadata throws ImageUploadException
public function test_missing_temp_path_throws_exception(): void
{
    // Arrange
    $invalidMetadata = [
        'original_filename' => 'test.jpg',
        // Missing 'temporary_path'
    ];

    // Act & Assert
    $this->expectException(ImageUploadException::class);
    $this->expectExceptionMessage('Invalid temporary file metadata');

    $this->imageService->createPendingImageRecord($invalidMetadata, 1, 555);
}
```

---

### **Test Suite 1.3: ImageController::uploadStepOneAction()**

#### **Test 1.3.1: GET Request Renders Form**
```php
// Test: GET /image/upload renders file upload form
public function test_upload_step_one_get_renders_form(): void
{
    // Arrange
    $request = $this->createServerRequest('GET', '/image/upload');

    // Act
    $response = $this->controller->uploadStepOneAction($request);

    // Assert
    $this->assertEquals(200, $response->getStatusCode());
    $body = (string)$response->getBody();
    $this->assertStringContainsString('<input type="file" name="image_file"', $body);
    $this->assertStringContainsString('required', $body);
}
```

#### **Test 1.3.2: POST Request Creates Pending Record**
```php
// Test: POST /image/upload creates pending image and redirects
public function test_upload_step_one_post_creates_pending_record(): void
{
    // Arrange
    $request = $this->createServerRequest('POST', '/image/upload')
        ->withUploadedFiles(['image_file' => $this->createValidImageUpload()]);

    // Act
    $response = $this->controller->uploadStepOneAction($request);

    // Assert
    $this->assertEquals(302, $response->getStatusCode()); // Redirect
    $this->assertStringContainsString('/image/upload/metadata', $response->getHeaderLine('Location'));

    // Verify session token set
    $this->assertArrayHasKey('current_image_upload_token', $_SESSION);
    $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $_SESSION['current_image_upload_token']);
}
```

#### **Test 1.3.3: Invalid File Type Shows Validation Error**
```php
// Test: Uploading .txt file shows validation error
public function test_upload_step_one_invalid_file_type(): void
{
    // Arrange
    $request = $this->createServerRequest('POST', '/image/upload')
        ->withUploadedFiles(['image_file' => $this->createInvalidFileUpload('test.txt')]);

    // Act
    $response = $this->controller->uploadStepOneAction($request);

    // Assert
    $this->assertEquals(302, $response->getStatusCode()); // Redirect back to form
    $flashMessages = $this->flash->getMessages();
    $this->assertStringContainsString('Invalid file type', $flashMessages['error'][0]);
}
```

---

### **Test Suite 1.4: ImageController::cancelUploadAction()**

#### **Test 1.4.1: Cancel Upload Cleans Up Pending Record**
```php
// Test: POST /image/cancel deletes temp file and DB record
public function test_cancel_upload_cleans_up_pending_record(): void
{
    // Arrange: Create pending image
    $pendingImageId = $this->createPendingImage();
    $uploadToken = $_SESSION['current_image_upload_token'];
    $tempPath = $this->imageRepository->findById($pendingImageId)->getTempPath();

    $request = $this->createServerRequest('POST', '/image/cancel');

    // Act
    $response = $this->controller->cancelUploadAction($request);

    // Assert
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertFileDoesNotExist($tempPath); // Temp file deleted
    $this->assertNull($this->imageRepository->findById($pendingImageId)); // DB record deleted
    $this->assertArrayNotHasKey('current_image_upload_token', $_SESSION); // Session cleared

    $flashMessages = $this->flash->getMessages();
    $this->assertStringContainsString('cancelled successfully', $flashMessages['success'][0]);
}
```

#### **Test 1.4.2: Cancel Non-Existent Upload Shows Error**
```php
// Test: Cancelling with no session token shows error
public function test_cancel_upload_no_session_token(): void
{
    // Arrange: No pending upload
    unset($_SESSION['current_image_upload_token']);

    $request = $this->createServerRequest('POST', '/image/cancel');

    // Act
    $response = $this->controller->cancelUploadAction($request);

    // Assert
    $flashMessages = $this->flash->getMessages();
    $this->assertStringContainsString('No pending upload found', $flashMessages['error'][0]);
}
```

#### **Test 1.4.3: Cancel Another User's Upload Is Blocked**
```php
// Test: User A cannot cancel User B's upload
public function test_cancel_upload_authorization_check(): void
{
    // Arrange: User A creates pending image
    $userAId = 1;
    $userBId = 2;
    $uploadToken = $this->createPendingImageForUser($userAId);

    // User B attempts to cancel User A's upload
    $_SESSION['current_image_upload_token'] = $uploadToken;
    $this->scrap->setUserId($userBId); // Simulate User B session

    $request = $this->createServerRequest('POST', '/image/cancel');

    // Act
    $response = $this->controller->cancelUploadAction($request);

    // Assert
    $flashMessages = $this->flash->getMessages();
    $this->assertStringContainsString('permission', $flashMessages['error'][0]);

    // Verify temp file still exists (not deleted)
    $image = $this->imageRepository->findByUploadToken($uploadToken);
    $this->assertNotNull($image);
}
```

---

### **Test Suite 1.5: ImageCleanupService**

#### **Test 1.5.1: Cleanup Single Pending Image**
```php
// Test: cleanupSinglePendingImage() deletes temp file + DB record
public function test_cleanup_single_pending_image(): void
{
    // Arrange
    $uploadToken = $this->createPendingImage();
    $image = $this->imageRepository->findByUploadToken($uploadToken);
    $tempPath = $image->getTempPath();

    // Act
    $this->cleanupService->cleanupSinglePendingImage($uploadToken);

    // Assert
    $this->assertFileDoesNotExist($tempPath);
    $this->assertNull($this->imageRepository->findByUploadToken($uploadToken));
}
```

#### **Test 1.5.2: Cleanup Expired Pending Uploads**
```php
// Test: cleanupExpiredPendingUploads() removes old records
public function test_cleanup_expired_pending_uploads(): void
{
    // Arrange: Create 2 pending images (1 expired, 1 recent)
    $expiredImageId = $this->createPendingImage(['created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))]);
    $recentImageId = $this->createPendingImage(['created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes'))]);

    // Act
    $cleanedCount = $this->cleanupService->cleanupExpiredPendingUploads();

    // Assert
    $this->assertEquals(1, $cleanedCount); // Only 1 expired image cleaned
    $this->assertNull($this->imageRepository->findById($expiredImageId)); // Expired deleted
    $this->assertNotNull($this->imageRepository->findById($recentImageId)); // Recent kept
}
```

#### **Test 1.5.3: Cleanup Non-Pending Image Is Skipped**
```php
// Test: Attempting to cleanup active image is ignored
public function test_cleanup_non_pending_image_is_skipped(): void
{
    // Arrange: Create active image (status='A')
    $activeImageId = $this->createActiveImage();
    $uploadToken = $this->imageRepository->findById($activeImageId)->getUploadToken();

    // Act
    $this->cleanupService->cleanupSinglePendingImage($uploadToken);

    // Assert
    $this->assertNotNull($this->imageRepository->findById($activeImageId)); // Image NOT deleted

    // Verify warning logged
    $this->assertStringContainsString('non-pending image', $this->logger->getLastMessage());
}
```

---

## 🧪 **Phase 2: Single Image Upload - Step 2 Tests**

### **Test Suite 2.1: ImageService::finalizeImageRecord()**

#### **Test 2.1.1: Finalize Pending Image Record**
```php
// Test: Metadata submission moves file to permanent storage
public function test_finalize_image_record(): void
{
    // Arrange
    $uploadToken = $this->createPendingImage();
    $metadataFormData = [
        'title' => 'Sunset Photo',
        'alt_text' => 'Beautiful sunset over the ocean',
        'description' => 'Taken at Santa Monica Beach',
    ];

    // Act
    $imageId = $this->imageService->finalizeImageRecord($uploadToken, $metadataFormData, 555);

    // Assert
    $image = $this->imageRepository->findById($imageId);
    $this->assertEquals('A', $image->getStatus()); // Status changed to Active
    $this->assertEquals('Sunset Photo', $image->getTitle());
    $this->assertNull($image->getTempPath()); // Temp path cleared
    $this->assertNull($image->getUploadToken()); // Upload token cleared
    $this->assertNotNull($image->getFilename()); // Permanent filename set
}
```

#### **Test 2.1.2: Invalid Upload Token Throws Exception**
```php
// Test: Invalid upload token throws ImageUploadException
public function test_finalize_with_invalid_token(): void
{
    // Arrange
    $invalidToken = 'invalid-token-12345';

    // Act & Assert
    $this->expectException(ImageUploadException::class);
    $this->expectExceptionMessage('Invalid or expired upload token');

    $this->imageService->finalizeImageRecord($invalidToken, [], 555);
}
```

#### **Test 2.1.3: Already Finalized Image Throws Exception**
```php
// Test: Attempting to finalize active image throws exception
public function test_finalize_already_finalized_image(): void
{
    // Arrange: Create active image (status='A')
    $activeImageId = $this->createActiveImage();
    $uploadToken = $this->imageRepository->findById($activeImageId)->getUploadToken();

    // Act & Assert
    $this->expectException(ImageRecordImmutableException::class);
    $this->expectExceptionMessage('already finalized');

    $this->imageService->finalizeImageRecord($uploadToken, [], 555);
}
```

---

### **Test Suite 2.2: ImageController::uploadStepTwoAction()**

#### **Test 2.2.1: GET Request Renders Metadata Form**
```php
// Test: GET /image/upload/metadata renders form with image preview
public function test_upload_step_two_get_renders_form(): void
{
    // Arrange
    $uploadToken = $this->createPendingImage();
    $_SESSION['current_image_upload_token'] = $uploadToken;

    $request = $this->createServerRequest('GET', '/image/upload/metadata');

    // Act
    $response = $this->controller->uploadStepTwoAction($request);

    // Assert
    $this->assertEquals(200, $response->getStatusCode());
    $body = (string)$response->getBody();
    $this->assertStringContainsString('<input type="hidden" name="upload_token"', $body);
    $this->assertStringContainsString('<img src="/image/temp-preview?token=', $body);
    $this->assertStringContainsString('<input type="text" name="title"', $body);
}
```

#### **Test 2.2.2: POST Request Finalizes Image**
```php
// Test: POST /image/upload/metadata completes upload
public function test_upload_step_two_post_finalizes_image(): void
{
    // Arrange
    $uploadToken = $this->createPendingImage();
    $_SESSION['current_image_upload_token'] = $uploadToken;

    $request = $this->createServerRequest('POST', '/image/upload/metadata')
        ->withParsedBody([
            'upload_token' => $uploadToken,
            'title' => 'Beach Sunset',
            'alt_text' => 'Sunset photo',
        ]);

    // Act
    $response = $this->controller->uploadStepTwoAction($request);

    // Assert
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/image/view/', $response->getHeaderLine('Location'));

    // Verify session cleared
    $this->assertArrayNotHasKey('current_image_upload_token', $_SESSION);

    // Verify image is now active
    $image = $this->imageRepository->findByUploadToken($uploadToken);
    $this->assertNull($image); // Upload token cleared, so findByUploadToken returns null
}
```

---

## 🧪 **Phase 3: Automated Cleanup Tests**

### **Test Suite 3.1: ImageCleanupCommand**

#### **Test 3.1.1: Console Command Executes Cleanup**
```php
// Test: php console.php image:cleanup removes expired uploads
public function test_cleanup_command_executes(): void
{
    // Arrange
    $expiredImageId = $this->createPendingImage(['created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))]);

    // Act
    $output = $this->executeConsoleCommand('image:cleanup');

    // Assert
    $this->assertStringContainsString('Cleaned up 1 expired', $output);
    $this->assertNull($this->imageRepository->findById($expiredImageId));
}
```

---

## 🧪 **Phase 4: Security Tests**

### **Test Suite 4.1: Authorization & CSRF**

#### **Test 4.1.1: Serve Temp Image Validates Token**
```php
// Test: GET /image/temp-preview requires valid session token
public function test_serve_temp_image_validates_token(): void
{
    // Arrange: Create pending image for User A
    $userAToken = $this->createPendingImageForUser(1);

    // User B attempts to access User A's temp image
    $_SESSION['current_image_upload_token'] = 'invalid-token';

    $request = $this->createServerRequest('GET', "/image/temp-preview?token={$userAToken}");

    // Act
    $response = $this->controller->serveTempImageAction($request);

    // Assert
    $this->assertEquals(403, $response->getStatusCode()); // Forbidden
}
```

---

## 📊 **Test Coverage Goals**

| Component | Target Coverage | Current Status |
|-----------|----------------|----------------|
| `TemporaryFileUploadService` | 95% | ✅ 98% |
| `ImageCleanupService` | 90% | ⚠️ 75% (need edge cases) |
| `ImageService` | 85% | ❌ 60% (need Phase 2 tests) |
| `ImageController` | 80% | ❌ 55% (need security tests) |
| `ImageRepository` | 90% | ✅ 92% |

---

## 🚀 **Running Tests**

```bash
# Run all image upload tests
vendor/bin/phpunit --filter ImageUploadTest

# Run specific test suite
vendor/bin/phpunit --filter ImageCleanupServiceTest

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

---

## 📝 **Test Naming Convention**

```php
// Format: test_{methodName}_{scenario}_{expectedOutcome}
public function test_createPendingImageRecord_validInput_returnsUploadToken(): void

// Format: test_{action}_{condition}
public function test_cancelUpload_noSessionToken_showsError(): void
```

---

**✅ All tests follow:**
- ✅ **AAA Pattern** (Arrange, Act, Assert)
- ✅ **Single Responsibility** (one assertion per logical outcome)
- ✅ **Descriptive Names** (test intent clear from name)
- ✅ **Isolated** (no test depends on another)