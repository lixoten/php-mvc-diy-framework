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
        $rawData = $data; // Keep a copy of the original scalar user input


        // Extract and validate CSRF token
        $token = $data['csrf_token'] ?? '';
        unset($data['csrf_token']);
        if (!$form->validateCSRFToken($token)) {
            $form->addError('_form', 'Invalid form submission.');
            return false;
        }

        // if (! empty($uploadedFiles)) {
        //     foreach ($uploadedFiles as $fieldName => $uploadedFile) {
        //         // Skip if no file uploaded
        //         if (!$uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface
        //             || $uploadedFile->getError() === \UPLOAD_ERR_NO_FILE
        //         ) {
        //             continue;
        //         }

        //         // Get field definition
        //         $field = $form->getField($fieldName);
        //         if (!$field) {
        //             continue;
        //         }

        //         // Get storeId from form context (or default to 1)
        //         // $storeId = $form->getContext()['store_id'] ?? 1;
        //         //$storeId = $form->getData()['store_id'] ?? 1;
        //         $storeId = $form->getContext()['store_id']; // fixme

        //         // Upload image using ImageStorageService
        //         try {
        //             $imageMetadata = $this->imageStorageService->upload($uploadedFile, $storeId);

        //             // Store hash in database (e.g., 'abc123def456...xyz')
        //             $data['filename'] = $imageMetadata['filename'];
        //             $extraProcessedData['image_metadata'] = $imageMetadata; //fixme lllllllllllllll

        //             $this->logger?->info('Image uploaded successfully', [
        //                 'field' => $fieldName,
        //                 'hash' => $imageMetadata['hash'],
        //                 'extension' => $imageMetadata['extension'],
        //             ]);
        //         } catch (\Throwable $e) {
        //             $this->logger?->error('Image upload failed', [
        //                 'field' => $fieldName,
        //                 'error' => $e->getMessage(),
        //             ]);

        //             // Add validation error to field
        //             // $field->addError('Image upload failed. Please try again.');
        //             $form->addError($data[$fieldName], 'Image upload failed. Please try again.');
        //         }
        //     }
        // }


        // --- File upload handling ---
        if (! empty($uploadedFiles)) {
            // ✅ Use the generic FileUploadService to handle files and get temporary metadata
            // This method should return an array of processed file info, keyed by fieldName
            $fileProcessedMetadata = $this->fileUploadService->handleFiles($uploadedFiles);

            // ✅ Store the generic temporary file metadata in extraProcessedData
            // This structure allows domain services (like ImageService) to retrieve
            // the temporary file paths and info for further processing.
            // We use a prefix like '_uploaded_file_temp_info' to clearly separate it.
            $extraProcessedData['_uploaded_file_temp_info'] = [];
            foreach ($uploadedFiles as $fieldName => $uploadedFile) {
                // If a file for this field was processed by the fileUploadService, add its metadata
                // Also handle cases where handleFiles returns an array of metadata for multi-file inputs
                if (isset($fileProcessedMetadata[$fieldName])) {
                    $extraProcessedData['_uploaded_file_temp_info'][$fieldName] = $fileProcessedMetadata[$fieldName];
                } else {
                    // For files that were attempted but not processed by the service (e.g., UPLOAD_ERR_NO_FILE
                    // might be filtered by the service if it only returns 'success' or 'failed' status),
                    // or if the service simply didn't return metadata for it due to an error.
                    if (
                        $uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface
                        && $uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE
                        && $uploadedFile->getError() !== \UPLOAD_ERR_OK // Also capture PHP errors here if service didn't
                    ) {
                        // Store error info directly from the raw UploadedFileInterface if the service didn't handle it
                        $errorMessage = $this->getPhpUploadErrorMessage($uploadedFile->getError());
                        $extraProcessedData['_uploaded_file_temp_info'][$fieldName] = [
                            'status' => 'failed',
                            'error_code' => $uploadedFile->getError(),
                            'error_message' => $errorMessage,
                            'original_filename' => $uploadedFile->getClientFilename(),
                            'mime_type' => $uploadedFile->getClientMediaType(),
                            'size_bytes' => $uploadedFile->getSize(),
                            'temporary_path' => null, // No temp path if failed early
                        ];
                    }
                }
            }

            // ✅ Handle generic PHP upload errors (e.g., file too large)
            foreach ($uploadedFiles as $fieldName => $uploadedFile) {
                if (
                    $uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface
                    && $uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE
                    && $uploadedFile->getError() !== \UPLOAD_ERR_OK
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
                }
            }
        }




        // // Sanitize submitted data using the DataSanitizer
        // $data = $this->dataSanitizer->sanitize($data, $form->getFields());



        // Determine if this is a security-critical form
        $isSecurityCritical = $form->getSecurityLevel() === 'high';

        $captchaValid = true; // Initialize with a default value

        //DebugRt::j('1', '', "111-1"); // bingbing
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
                $captchaResponse = $data['g-recaptcha-response'] ?? $data['g-recaptcha'] ?? '';

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
        $this->dispatchEvent(FormEvents::PRE_SUBMIT, $form, $data);

        // Set form data
        $form->setData($data);
        $form->setExtraProcessedData($extraProcessedData);

        // Dispatch POST_SUBMIT event
        $this->dispatchEvent(FormEvents::POST_SUBMIT, $form, $data);

        // Dispatch PRE_VALIDATE event
        $this->dispatchEvent(FormEvents::PRE_VALIDATE, $form, $data);

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
