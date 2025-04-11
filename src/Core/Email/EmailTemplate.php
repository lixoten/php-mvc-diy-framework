<?php

declare(strict_types=1);

namespace Core\Email;

/**
 * Email template helper class
 */
class EmailTemplate
{
    /**
     * Common email template wrapper
     *
     * @param string $content Main content section of the email
     * @param string $title Email title (optional)
     * @param array $options Additional template options
     * @return string Final HTML email
     */
    public static function wrap(string $content, string $title = '', array $options = []): string
    {
        $siteName = $options['site_name'] ?? 'MVCLixo';
        $siteUrl = $options['site_url'] ?? 'http://mvclixo.tv';
        $accentColor = $options['accent_color'] ?? '#3490dc';
        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$title}</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f8f8;
                }
                .email-wrapper {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #fff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .email-header {
                    background-color: {$accentColor};
                    padding: 20px 30px;
                    text-align: center;
                }
                .email-header img {
                    max-width: 200px;
                    height: auto;
                }
                .email-header h1 {
                    color: white;
                    margin: 0;
                    font-size: 24px;
                }
                .email-body {
                    padding: 30px;
                    background-color: #ffffff;
                }
                .email-footer {
                    padding: 20px 30px;
                    text-align: center;
                    font-size: 13px;
                    color: #888;
                    background-color: #f5f5f5;
                }
                .button {
                    display: inline-block;
                    background-color: {$accentColor};
                    color: #ffffff;
                    padding: 12px 24px;
                    border-radius: 4px;
                    text-decoration: none;
                    font-weight: bold;
                    margin: 15px 0;
                }
                @media screen and (max-width: 600px) {
                    .email-wrapper {
                        width: 100% !important;
                        border-radius: 0;
                    }
                    .email-body, .email-header, .email-footer {
                        padding: 15px !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-wrapper">
                <div class="email-header">
                    <h1>{$siteName}</h1>
                </div>
                <div class="email-body">
                    {$content}
                </div>
                <div class="email-footer">
                    &copy; {$year} {$siteName}. All rights reserved.<br>
                    <a href="{$siteUrl}" style="color: {$accentColor};">{$siteUrl}</a>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
