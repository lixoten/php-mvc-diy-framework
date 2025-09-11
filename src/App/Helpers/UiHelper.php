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

    public static function tableLinks(array $links, int $columns = 4): string
    {
        if (empty($links)) {
            return '';
        }

        $html = '<table class="table table-bordered"><tbody>';
        $total = count($links);
        $index = 0;

        while ($index < $total) {
            $html .= '<tr>';
            for ($col = 0; $col < $columns; $col++) {
                if ($index < $total) {

                    $link = $links[$index];
                    if (
                        isset($link['url']) &&
                        $link['url'] instanceof \App\Enums\Url &&
                        $link['url']->name === 'CORE_TESTY_EDIT'
                    ) {
                        $rrr = 1;
                        // This is the CORE_TESTY link
                        $html .= '<td>' . \App\Helpers\LinkBuilder::generateTextLink($link['url'], ['id' => 22], $link['text']) . '</td>';
                    } else {
                        $html .= '<td>' . \App\Helpers\LinkBuilder::generateTextLink($link['url'], [], $link['text']) . '</td>';
                    }
                    $index++;
                } else {
                    $html .= '<td></td>';
                }
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
