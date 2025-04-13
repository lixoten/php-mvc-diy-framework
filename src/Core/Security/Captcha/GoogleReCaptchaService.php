<?php

declare(strict_types=1);

namespace Core\Security\Captcha;

use App\Helpers\DebugRt;
use Core\Security\BruteForceProtectionService;

class GoogleReCaptchaService implements CaptchaServiceInterface
{
    private string $siteKey;
    private string $secretKey;
    private ?BruteForceProtectionService $bruteForceService; // foofee
    private array $config;

    /**
     * Constructor
     *
     * @param string $siteKey Google reCAPTCHA site key
     * @param string $secretKey Google reCAPTCHA secret key
     * @param BruteForceProtectionService $bruteForceService Rate limiting service
     * @param array $config Additional configuration options
     */
    public function __construct(
        string $siteKey,
        string $secretKey,
        ?BruteForceProtectionService $bruteForceService = null,  // Make optional // foofee
        array $config = []
    ) {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->bruteForceService = $bruteForceService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        //DebugRt::j('0', '', "111-4"); // bingbing
        return $this->config['enabled'] ?? true;
    }


    /**
     * {@inheritdoc}
     */
    public function isRequired(string $actionType, ?string $identifier = null): bool
    {
        // First check if CAPTCHA is globally enabled
        //DebugRt::j('0', '', "111-2"); // bingbing
        if (!($this->config['enabled'] ?? true)) {
            return false;
        }
        //DebugRt::j('0', 'Captcha Config', $this->config['enabled']); // bingbing

        if (empty($identifier)) {
            $identifier = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        // TEMPORARY FIX: During BruteForce service transition
        // Check if bruteForceService property is null or not set
        if (!isset($this->bruteForceService)) {
            DebugRt::j('0', '', 555);
            return in_array($actionType, ['login', 'registration', 'password_reset']);
        }
        DebugRt::j('1', '', 777);

        $threshold = $this->config['thresholds'][$actionType] ?? 3;

        // If threshold is 0, CAPTCHA is never required
        if ($threshold <= 0) {
            return false;
        }

        // Get action-specific config to determine lockout time
        $actionConfig = $this->bruteForceService->getConfigForActionType($actionType);
        if (!$actionConfig) {
            return false;
        }

        // Count recent failed attempts
        $cutoffTime = time() - $actionConfig['lockout_time'];
        $attempts = $this->bruteForceService->getAttemptCount($identifier, $actionType, $cutoffTime);

        // Return true if attempts exceed threshold
        return $attempts >= $threshold;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $formId = null, array $options = []): string
    {
        $version = $this->config['version'] ?? 'v2';

        if ($version === 'v3') {
            // Invisible reCAPTCHA v3
            return sprintf(
                '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-%s">',
                $formId ?? 'default'
            );
        } else {
            // Standard reCAPTCHA v2 checkbox
            $size = $options['size'] ?? 'normal';
            $theme = $options['theme'] ?? 'light';

            return sprintf(
                '<div class="g-recaptcha"
                    data-sitekey="%s"
                    data-theme="%s"
                    data-size="%s"
                    %s
                ></div>',
                htmlspecialchars($this->siteKey),
                htmlspecialchars($theme),
                htmlspecialchars($size),
                $formId ? 'data-form-id="' . htmlspecialchars($formId) . '"' : ''
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $response): bool
    {
        // Add debugging to see what's happening
        //DebugRt::j('1', 'captcha-response', $response);

        // If CAPTCHA is disabled, always return true (consider verification successful)
        //DebugRt::j('1', '', "111-3"); // bingbing
        if (!($this->config['enabled'] ?? true)) {
            return true;
        }

        if (empty($response)) {
            return false;
        }

        $data = [
            'secret' => $this->secretKey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $captchaResponse = json_decode($verify);

        if ($captchaResponse->success) {
            // For v3, check score threshold
            if (isset($captchaResponse->score)) {
                $minScore = $this->config['score_threshold'] ?? 0.5;
                return $captchaResponse->score >= $minScore;
            }
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getScripts(): string
    {
        $version = $this->config['version'] ?? 'v2';
        $url = 'https://www.google.com/recaptcha/api.js';

        if ($version === 'v3') {
            $url .= '?render=' . urlencode($this->siteKey);
        }

        $script = sprintf('<script src="%s" async defer></script>', $url);

        if ($version === 'v3') {
            $script .= <<<HTML
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                grecaptcha.ready(function() {
                    grecaptcha.execute('{$this->siteKey}', {action: 'submit'}).then(function(token) {
                        document.getElementById('g-recaptcha-response-default').value = token;
                    });
                });
            });
            </script>
            HTML;
        }

        return $script;
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }
}
