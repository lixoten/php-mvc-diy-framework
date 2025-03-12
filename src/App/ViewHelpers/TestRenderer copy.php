<?php

declare(strict_types=1);

namespace App\ViewHelpers;

class TestRenderer
{
    public function renderTest(string $text): string
    {
        $output = '<div style="border:2px solid red;">';
        $output .= $text . 'BOOOOMBOOOOMBOOOOMBOOOOMBOOOOMBOOOOMBOOOOMBOOOOMBOOOOM';
        $output .= '</div>';

        return $output;
    }
}
