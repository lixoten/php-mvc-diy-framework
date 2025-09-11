<?php

declare(strict_types=1);

namespace App\Enums;

enum AttentionType
{
    case DANGER;
    case CRAP;
    case FALLBACK;
    case Error;

    public function errorMessage(string $msg_in = ''): string
    {
        // Get the line number where this method was called from
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerLine = $backtrace[0]['line'] ?? 'unknown';
        $callerFile = basename($backtrace[0]['file'] ?? 'unknown');

        $locationInfo = "Line: {$callerLine} File: {$callerFile}";

        $msg = empty($msg_in) ? $locationInfo : " {$msg_in} {$locationInfo}";
        
        return match ($this) {
            self::DANGER => 'Danger Danger will robinson.' . $msg,
            self::CRAP => 'CRAP CRAP will robinson.' . $msg,
            self::FALLBACK => 'Danger Danger you are Using a Fallback!!!' . $msg
        };
    }
}
