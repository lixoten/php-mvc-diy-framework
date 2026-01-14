<?php

declare(strict_types=1);

namespace Core\Form;

use App\Helpers\DebugRt;
use Core\Form\CSRF\CSRFToken;
use Core\Form\Event\FormEvent;
use Core\Form\Event\FormEvents;
use Core\Form\Upload\FileUploadServiceInterface;
use Core\Form\Validation\ValidatorRegistry;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Services\FormatterService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use SebastianBergmann\Environment\Console;
use Core\Form\DataSanitizer;
use Core\Services\DataNormalizerService;
use Core\Services\ImageStorageServiceInterface;

/**
 * Form handler implementation
 */
class FormHandler implements FormHandlerInterface
{
    //private CSRFToken $csrf; // TODO-do we need
    private ?EventDispatcherInterface $eventDispatcher;
    private ?CaptchaServiceInterface $captchaService;
    //private ValidatorRegistry $validatorRegistry; // TODO-do we need
    private FileUploadServiceInterface $fileUploadService;

    //  * @param CSRFToken $csrf
    /**
     * Constructor
     *
     * @param ValidatorRegistry $validatorRegistry
     * @param FormatterService $formatterService
     * @param DataSanitizer $dataSanitizer
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        //ValidatorRegistry $validatorRegistry,
        private FormatterService $formatterService,
        private DataSanitizer $dataSanitizer,
        private DataNormalizerService $dataNormalizerService,
        // private ImageStorageServiceInterface $imageStorageService,
        FileUploadServiceInterface $fileUploadService,
        ?CaptchaServiceInterface $captchaService = null,
        ?EventDispatcherInterface $eventDispatcher = null,
        private ?\Psr\Log\LoggerInterface $logger = null,
    ) {
        //$this->validatorRegistry = $validatorRegistry;
        $this->formatterService = $formatterService;
        $this->dataSanitizer = $dataSanitizer;
        $this->dataNormalizerService = $dataNormalizerService;
        $this->captchaService = $captchaService;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileUploadService = $fileUploadService;
        // $this->imageStorageService = $imageStorageService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(FormInterface $form, ServerRequestInterface $request): bool
    {
        // Only process if it's a POST request
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        // Parse form data from the request (keeps files separate)
        $parsed = $this->parseRequestData($request);
        $extraProcessedData = [];
        $data = $parsed['data'] ?? [];
        $uploadedFiles = $parsed['files'] ?? [];
        // DebugRt::j('0', 'uploadedFiles : ', $uploadedFiles);
        $rawData = $data; // Keep a copy of the original scalar user input

        // // --- File upload handling ---
        // // Files that were actually uploaded or had severe PHP errors (not UPLOAD_ERR_NO_FILE)
        // $filesToProcessByService = [];
        // // Will store UploadedFileInterface objects in $data for validators
        // $fileInputsDataForValidation = [];
        // // Flag to indicate if any critical PHP upload errors were detected
        // $criticalPhpUploadErrorsDetected = false;

        // foreach ($uploadedFiles as $fieldName => $uploadedFile) {
        //     if ($uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface) {
        //         // ✅ Always pass the UploadedFileInterface object to the form data
        //         // This makes it available for the FileValidator to inspect.
        //         $fileInputsDataForValidation[$fieldName] = $uploadedFile;

        //         // ✅ If no file was selected (UPLOAD_ERR_NO_FILE), do NOT pass it to FileUploadService.
        //         // The service should only handle files that actually need to be moved/processed.
        //         if ($uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE) {
        //             $filesToProcessByService[$fieldName] = $uploadedFile;
        //         }

        //         // ✅ Catch generic PHP upload errors (like file too large, partial upload) that are NOT UPLOAD_ERR_NO_FILE.
        //         // These are critical system errors that should fail immediately,
        //         // regardless of whether the field is marked as 'required'.
        //         if (
        //             $uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE &&
        //             $uploadedFile->getError() !== \UPLOAD_ERR_OK
        //         ) {
        //             $errorMessage = $this->getPhpUploadErrorMessage($uploadedFile->getError());
        //             $form->addError(
        //                 $fieldName,
        //                 'File upload failed: ' . $errorMessage . ' (' . $uploadedFile->getError() . ')'
        //             );
        //             $this->logger?->error('Generic file upload failed (PHP error)', [
        //                 'field' => $fieldName,
        //                 'error_code' => $uploadedFile->getError(),
        //                 'error_message' => $errorMessage,
        //             ]);
        //             $criticalPhpUploadErrorsDetected = true; // Mark that a critical PHP error occurred
        //         }
        //     }
        // }

        // // ✅ If any critical PHP upload errors (not UPLOAD_ERR_NO_FILE) were detected, stop processing the form.
        // // These are severe enough to warrant an immediate return.
        // if ($criticalPhpUploadErrorsDetected) {
        //     $form->setData($rawData); // Restore raw data for redisplay
        //     $this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data);
        //     return false;
        // }

        // // ✅ Only call FileUploadService if there are actual files to move/process
        // if (!empty($filesToProcessByService)) {
        //     // Use the generic FileUploadService to handle files and get temporary metadata
        //     $fileProcessedMetadata = $this->fileUploadService->handleFiles($filesToProcessByService);
        //     DebugRt::j('0', 'filesToProcessByService', $filesToProcessByService);

        //     // Store the generic temporary file metadata in extraProcessedData
        //     $extraProcessedData['_uploaded_file_temp_info'] = [];
        //     foreach ($fileProcessedMetadata as $fieldName => $metadata) {
        //         $extraProcessedData['_uploaded_file_temp_info'][$fieldName] = $metadata;
        //     }
        // }


        $fileProcessingResult = $this->processIncomingUploadedFiles($form, $uploadedFiles);

        $fileInputsDataForValidation = $fileProcessingResult['fileInputsForValidation'];
        $criticalPhpUploadErrorsDetected = $fileProcessingResult['hasCriticalPhpErrors'];
        $processedTempFileInfo = $fileProcessingResult['tempFileInfo'];

        // Existing logic check for critical errors, remains as is but uses the variable from helper
        if ($criticalPhpUploadErrorsDetected) {
            $form->setData($rawData); // Restore raw data for redisplay
            $this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data); // Uses $data as per your current code
            return false;
        }

        // Populate extraProcessedData with temporary file info if available
        if (!empty($processedTempFileInfo)) {
            $extraProcessedData['_uploaded_file_temp_info'] = $processedTempFileInfo;
        }



        // ✅ Merge the UploadedFileInterface objects into the data array *before* setting data on the form.
        // This makes them available for the FieldInterface and subsequently for the Validator.
        // Note: The key in $data should match the field name in the config (e.g., 'filename').

        // if ($fileInputsDataForValidation['filename']->getError() === 4) {
        //     $fileInputsDataForValidation['filename'] = '';
        //     $data = array_merge($data, $fileInputsDataForValidation);
        // } else {
        //     $data = array_merge($data, $fileInputsDataForValidation);
        // }

        // Create a new array $dataForValidation that merges original scalar data with file objects for validation.
        // This ensures that the Form object (and its fields) holds UploadedFileInterface objects for FileValidator,
        // but the original $data (scalar post data) can be safely used for final processing.
        $dataForValidation = $data; // Start with scalar data
        if (!empty($fileInputsDataForValidation)) {
            foreach ($fileInputsDataForValidation as $fieldName => $fileValue) {
                // If UPLOAD_ERR_NO_FILE, convert to empty string for consistent validator input.
                // Otherwise, keep the UploadedFileInterface object for FileValidator to inspect its error state.
                if ($fileValue instanceof \Psr\Http\Message\UploadedFileInterface && $fileValue->getError() === UPLOAD_ERR_NO_FILE) {
                    $dataForValidation[$fieldName] = '';
                } else {
                    $dataForValidation[$fieldName] = $fileValue;
                }
            }
        }



        // // Sanitize submitted data using the DataSanitizer
        // $data = $this->dataSanitizer->sanitize($data, $form->getFields());





        // // Extract and validate CSRF token
        // $token = $data['csrf_token'] ?? '';
        // unset($data['csrf_token']);
        // if (!$form->validateCSRFToken($token)) {
        //     $form->addError('_form', 'Invalid form submission.');
        //     return false;
        // }

        // Extract and validate CSRF token (using $dataForValidation)
        $token = $dataForValidation['csrf_token'] ?? '';
        unset($dataForValidation['csrf_token']); // Remove from dataForValidation
        if (!$form->validateCSRFToken($token)) {
            $form->addError('_form', 'Invalid form submission.');
            $form->setData($rawData); // Reset to raw data on CSRF failure
            $this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $rawData); // Use rawData for event
            return false;
        }




        // Determine if this is a security-critical form
        $isSecurityCritical = $form->getSecurityLevel() === 'high';

        $captchaValid = true; // Initialize with a default value

        // If CAPTCHA is disabled globally, skip validation
        // Handle special fields like reCAPTCHA
        if ($form->hasField('captcha')) {
            // Skip CAPTCHA validation if service doesn't exist or is disabled
            if (!$this->captchaService || !$this->captchaService->isEnabled()) {
                // CAPTCHA validation skipped - continue with form processing
            } else {
                // CAPTCHA is enabled and required - validate it
                //$captchaResponse = $request->getParsedBody()['g-recaptcha-response'] ?? '';
                // prefer sanitized/parsed data (already extracted earlier)
                $captchaResponse = $dataForValidation['g-recaptcha-response'] ?? $dataForValidation['g-recaptcha'] ?? '';

                // If CAPTCHA response is empty, mark form as invalid
                if (empty($captchaResponse)) {
                    $form->addError('captcha', 'Please complete the security check.');
                    //$form->setData($data);
                    //$this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data);
                    //return false;
                    $captchaValid = false;
                } elseif (!$this->captchaService->verify($captchaResponse)) { // Verify CAPTCHA response
                    $form->addError('captcha', 'CAPTCHA verification failed.');
                    //#form->setData($data);
                    //$this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data);
                    //return false;
                    $captchaValid = false;
                }
                DebugRt::j('0', 'Security is: ', $form->getSecurityLevel());

                // For security-critical forms, return early if CAPTCHA fails
                if (!$captchaValid && $isSecurityCritical) {
                    $form->setData($data);
                    $this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data);
                    //DebugRt::j('1', '', $isSecurityCritical);
                    DebugRt::j('0', 'Security is: ', $form->getSecurityLevel());
                    return false;
                }
            }
        }


        // // Extract and validate CSRF token
        // $token = $data['csrf_token'] ?? '';
        // unset($data['csrf_token']);
        // if (!$form->validateCSRFToken($token)) {
        //     $form->addError('_form', 'Invalid form submission.');
        //     return false;
        // }

        // Dispatch PRE_SUBMIT event
        $this->dispatchEvent(FormEvents::PRE_SUBMIT, $form, $dataForValidation);

        // Set form data
        $form->setData($dataForValidation);
        $form->setExtraProcessedData($extraProcessedData);

        // Dispatch POST_SUBMIT event
        $this->dispatchEvent(FormEvents::POST_SUBMIT, $form, $dataForValidation);

        // Dispatch PRE_VALIDATE event
        $this->dispatchEvent(FormEvents::PRE_VALIDATE, $form, $dataForValidation);

        // $geoLocatio  n = $request->getAttribute('geo_location');
        // $regionCode = $geoLocation['countryCode'] ?? 'fookville'; // Fixme 3


        // Inject user_region into formatter options for the telephone field
        // $fields = $form->getFields();
        // if (isset($fields['telephone']['formatter'][0]['options'])) {
        //     $fields['telephone']['formatter'][0]['options']['user_region'] = $regionCode;
        //     $form->setFields($fields);
        // }


        // $isValid = $form->validate(['region' => $regionCode]);
        $isValid = $form->validate();

        // If validation fails, redisplay the original user input
        if (!$isValid) {
            $form->setData($rawData);
        } else {
            // If validation passes, NOW sanitize and normalize the data for storage

            // 1. Run the data through your sanitizer
            $data = $this->dataSanitizer->sanitize($data, $form->getFields());

            // If validation passes, set sanitized data for storage
            // 2. Run the normalized data through your normalizer (coercing types, etc.)
            $data = $this->dataNormalizerService->normalize($data, $form->getFields());

            // 3. Set the final, clean data for storage
            $form->setData($data);
        }

        // Dispatch POST_VALIDATE event
        $this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data);

        // return $isValid;
        return $isValid && ($captchaValid ?? true);
    }



    /**
     * Helper method to process incoming uploaded files for the form.
     * Encapsulates logic for detecting PHP upload errors, preparing data for validators,
     * and coordinating with the FileUploadService for temporary storage.
     *
     * @param FormInterface $form The current form instance to add errors to.
     * @param array<string, \Psr\Http\Message\UploadedFileInterface|array<mixed>> $uploadedFiles Raw uploaded files from the request.
     * @return array{
     *     fileInputsForValidation: array<string, \Psr\Http\Message\UploadedFileInterface|string>,
     *     hasCriticalPhpErrors: bool,
     *     tempFileInfo: array<string, array<string, mixed>>
     * } Returns a structured array containing processed file data.
     */
    private function processIncomingUploadedFiles(FormInterface $form, array $uploadedFiles): array
    {
        // Files that were actually uploaded or had severe PHP errors (not UPLOAD_ERR_NO_FILE)
        $filesToProcessByService = [];
        // Will store UploadedFileInterface objects in $data for validators
        $fileInputsDataForValidation = [];
        // Flag to indicate if any critical PHP upload errors were detected
        $criticalPhpUploadErrorsDetected = false;
        // Store the generic temporary file metadata here before returning
        $tempFileInfo = [];

        foreach ($uploadedFiles as $fieldName => $uploadedFile) {
            if ($uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface) {
                // ✅ Always pass the UploadedFileInterface object to the form data
                // This makes it available for the FileValidator to inspect.
                $fileInputsDataForValidation[$fieldName] = $uploadedFile;

                // ✅ If no file was selected (UPLOAD_ERR_NO_FILE), do NOT pass it to FileUploadService.
                // The service should only handle files that actually need to be moved/processed.
                if ($uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE) {
                    $filesToProcessByService[$fieldName] = $uploadedFile;
                }

                // ✅ Catch generic PHP upload errors (like file too large, partial upload) that are NOT UPLOAD_ERR_NO_FILE.
                // These are critical system errors that should fail immediately,
                // regardless of whether the field is marked as 'required'.
                if (
                    $uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE &&
                    $uploadedFile->getError() !== \UPLOAD_ERR_OK
                ) {
                    $errorMessage = $this->getPhpUploadErrorMessage($uploadedFile->getError());
                    $form->addError(
                        $fieldName,
                        'File upload failed: ' . $errorMessage . ' (' . $uploadedFile->getError() . ')'
                    );
                    $this->logger?->error('Generic file upload failed (PHP error)', [
                        'field' => $fieldName,
                        'error_code' => $uploadedFile->getError(),
                        'error_message' => $errorMessage,
                    ]);
                    $criticalPhpUploadErrorsDetected = true; // Mark that a critical PHP error occurred
                }
            }
        }

        // ✅ Only call FileUploadService if there are actual files to move/process
        if (!empty($filesToProcessByService)) {
            // Use the generic FileUploadService to handle files and get temporary metadata
            $fileProcessedMetadata = $this->fileUploadService->handleFiles($filesToProcessByService);
            DebugRt::j('0', 'filesToProcessByService', $filesToProcessByService);

            // Store the generic temporary file metadata in tempFileInfo
            foreach ($fileProcessedMetadata as $fieldName => $metadata) {
                $tempFileInfo[$fieldName] = $metadata;
            }
        }

        return [
            'fileInputsForValidation' => $fileInputsDataForValidation,
            'hasCriticalPhpErrors' => $criticalPhpUploadErrorsDetected,
            'tempFileInfo' => $tempFileInfo,
        ];
    }





    /**
     * Parse request data based on content type
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    private function parseRequestData(ServerRequestInterface $request): array
    {
        // Prefer parsed body for typical form submissions
        $parsedBody = $request->getParsedBody();
        $data = is_array($parsedBody) ? $parsedBody : [];

        $contentType = $request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            $content = (string) $request->getBody();
            $data = json_decode($content, true) ?? $data;
        }

        // For multipart, keep uploaded files separate from scalar data.
        // Normalize uploaded files to field => UploadedFileInterface | array(UploadedFileInterface)
        $uploadedFiles = [];
        if (strpos($contentType, 'multipart/form-data') !== false) {
            $rawFiles = $request->getUploadedFiles() ?? [];
            foreach ($rawFiles as $field => $value) {
                if (is_array($value)) {
                    // multi-file field or nested structure — keep as array of UploadedFileInterface
                    $uploadedFiles[$field] = $value;
                } else {
                    $uploadedFiles[$field] = $value;
                }
            }

            $parsedBody = $request->getParsedBody() ?? [];
            $data = is_array($parsedBody) ? $parsedBody : $data;
        }

        // Fallback: try parsing raw body into scalars
        if (empty($data)) {
            $content = (string) $request->getBody();
            if (!empty($content)) {
                parse_str($content, $parsed);
                if (is_array($parsed) && !empty($parsed)) {
                    $data = $parsed;
                }
            }
        }

        return [
            'data' => $data,
            'files' => $uploadedFiles,
        ];
    }

    /**
     * Returns a human-readable error message for a given PHP file upload error code.
     *
     * @param int $errorCode One of PHP's UPLOAD_ERR_XXX constants.
     * @return string The error message.
     */
    private function getPhpUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }


    /**
     * Dispatch a form event
     *
     * @param string $eventName The name of the event
     * @param FormInterface $form The form instance
     * @param mixed $data Additional data for the event
     */
    private function dispatchEvent(string $eventName, FormInterface $form, $data = null): void
    {
        if ($this->eventDispatcher) {
            $event = new FormEvent($eventName, $form, $data);
            $this->eventDispatcher->dispatch($event);
        }
    }
}
