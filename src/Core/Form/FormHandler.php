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
        private ImageStorageServiceInterface $imageStorageService,
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
        $this->imageStorageService = $imageStorageService;
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
        $data = $parsed['data'] ?? [];
        // $data['generic_color'] = '#errrrr'; // fixme - just for testing invalid color
        //$data['secret_code_hash'] = '-22'; // fixme - just for testing invalid color
        // $data['secret_code_hash'] = '12'; // fixme - just for testing invalid color
        // $data['generic_date'] = 22; // fixme - just for testing invalid color
        // $data['generic_datetime'] = 22; // fixme - just for testing invalid color
        // $data['generic_month'] = 22; // fixme - just for testing invalid color
        // $data['generic_week'] = 22; // fixme - just for testing invalid color
        // $data['generic_time'] = 22; // fixme - just for testing invalid color
        // $data['generic_number'] = 'w'; // fixme - just for testing invalid color
        // $data['generic_decimal'] = 'w'; // fixme - just for testing invalid color
        // $data['volume_level'] = 10; // fixme - just for testing invalid color
        // $data['start_rating'] = 1.51; // fixme - just for testing invalid color
        // $data['generic_color'] = "22"; // fixme - just for testing invalid color
        $uploadedFiles = $parsed['files'] ?? [];
        $rawData = $data; // Keep a copy of the original scalar user input


        // Extract and validate CSRF token
        $token = $data['csrf_token'] ?? '';
        unset($data['csrf_token']);
        if (!$form->validateCSRFToken($token)) {
            $form->addError('_form', 'Invalid form submission.');
            return false;
        }

        // --- File upload handling ---
        // if (! empty($uploadedFiles)) {
        //     // Validate/store uploaded files and get metadata per field
        //     $fileValues = $this->fileUploadService->handleFiles($uploadedFiles, $form->getFields());

        //     // ✅ DEBUG: Log file metadata returned
        //     // $this->logger?->debug('File upload results', [
        //     //     'metadata' => $fileValues,
        //     // ]);

        //     // ✅ NEW: Check if any uploads failed and add errors to form
        //     foreach ($uploadedFiles as $fieldName => $uploadedFile) {
        //         // Skip if this field wasn't processed (e.g., empty upload)
        //         if (!isset($fileValues[$fieldName])) {
        //             // Check if file upload was attempted but failed
        //             if ($uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface
        //                 && $uploadedFile->getError() !== \UPLOAD_ERR_NO_FILE
        //             ) {
        //                 // ✅ Add generic error to field
        //                 $field = $form->getField($fieldName);
        //                 if ($field) {
        //                     // $field->getErrors() addError('File upload failed. Please try again or choose a different file.');
        //                 }
        //             }
        //         }
        //     }


        //     // Normalize metadata into simple storage keys for existing form flows:
        //     // single file: ['key'=>...] -> 'field' => 'storage/key.ext'
        //     // multi file: [ ['key'=>...], ... ] -> 'field' => ['storage/one', 'storage/two']
        //     foreach ($fileValues as $field => $meta) {
        //         if (is_array($meta) && isset($meta['key'])) {
        //             $data[$field] = $meta['key'];
        //             continue;
        //         }

        //         if (is_array($meta) && isset($meta[0]) && is_array($meta[0]) && isset($meta[0]['key'])) {
        //             $keys = [];
        //             foreach ($meta as $m) {
        //                 if (is_array($m) && isset($m['key'])) {
        //                     $keys[] = $m['key'];
        //                 }
        //             }
        //             if (! empty($keys)) {
        //                 $data[$field] = $keys;
        //             }
        //         }
        //     }
        // }
        if (! empty($uploadedFiles)) {
            foreach ($uploadedFiles as $fieldName => $uploadedFile) {
                // Skip if no file uploaded
                if (!$uploadedFile instanceof \Psr\Http\Message\UploadedFileInterface
                    || $uploadedFile->getError() === \UPLOAD_ERR_NO_FILE
                ) {
                    continue;
                }

                // Get field definition
                $field = $form->getField($fieldName);
                if (!$field) {
                    continue;
                }

                // Get storeId from form context (or default to 1)
                // $storeId = $form->getContext()['store_id'] ?? 1;
                //$storeId = $form->getData()['store_id'] ?? 1;
                $storeId = 6; // fixme 

                // Upload image using ImageStorageService
                try {
                    $imageMetadata = $this->imageStorageService->upload($uploadedFile, $storeId);

                    // Store hash in database (e.g., 'abc123def456...xyz')
                    $data[$fieldName] = $imageMetadata['hash'];

                    $this->logger?->info('Image uploaded successfully', [
                        'field' => $fieldName,
                        'hash' => $imageMetadata['hash'],
                        'extension' => $imageMetadata['extension'],
                    ]);
                } catch (\Throwable $e) {
                    $this->logger?->error('Image upload failed', [
                        'field' => $fieldName,
                        'error' => $e->getMessage(),
                    ]);

                    // Add validation error to field
                    // $field->addError('Image upload failed. Please try again.');
                    $form->addError($data[$fieldName], 'Image upload failed. Please try again.');
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

        // Dispatch POST_SUBMIT event
        $this->dispatchEvent(FormEvents::POST_SUBMIT, $form, $data);

        // Dispatch PRE_VALIDATE event
        $this->dispatchEvent(FormEvents::PRE_VALIDATE, $form, $data);

        // Validate form
        // $geoLocation = $request->getAttribute('geo_location');
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
