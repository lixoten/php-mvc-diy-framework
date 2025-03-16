<?php

namespace App\Helpers;

class UiHelper
{
    public function ulLinks(?array $items, string $ulClass = '', string $liClass = ''): string
    {
        if ($items === null || !is_array($items) || empty($items)) {
            return ''; // Return empty string for invalid/empty input
        }

        $ulClassAttr = $ulClass ? ' class="' . HtmlHelper::escape($ulClass) . '"' : '';
        $liClassAttr = $liClass ? ' class="' . HtmlHelper::escape($liClass) . '"' : '';

        $liContent = '';
        foreach ($items as $key => $value) {
            $liContent .= "<li{$liClassAttr}><a href=\"\\" .
                          HtmlHelper::escape($value) . "\">" .
                          HtmlHelper::escape($key) . "</a></li>";
        }

        return "<ul{$ulClassAttr}>{$liContent}</ul>";
    }
}
