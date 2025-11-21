<?php

declare(strict_types=1);

namespace App\ViewHelpers;

use App\Helpers\DebugRt as Debug;
use App\Helpers\HtmlHelper;
use InvalidArgumentException;

class ListRenderer
{
    /**
     * Renders a list of records as a grid.
     * **$fields Detail Notes:**
     * - Defines the fields to be displayed in the list
     * - The keys represent the field names in the data source, What is in the database/forms with "_" underscores
     * - The values represent the labels to be displayed in the list
     *
     * @param array $records An array of records to render. Each record is an associative array.
     * @param array $fields  An associative array of fields to display for each record.
     *                       The keys in this array should correspond to the keys in the record arrays.
     *                       The values are the column headers to display.
     * @return string The HTML output.
     */
    public function renderList(array $records, array $fields): string
    {
        // Check if $fields is an associative array
        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                $flattenedFields = "<pre>";
                $flattenedFields .= print_r($fields, true);
                $flattenedFields .= "</pre>";
                $exceptionMessage = 'The $fields array must be fully associative.' . $flattenedFields;

                Debug::boom($exceptionMessage);
                throw new InvalidArgumentException($exceptionMessage, 99);
            }
        }

        // Generate the HTML table
        $htmlTable = $this->displayRecords($fields, $records);

        // Output the HTML
        return $htmlTable;
    }

    /**
     * Displays a list of records in a table format.
     *
     * @param array $header An associative array of column headers.
     * @param array $records An associative array of records, where each record is an array
     *                     with keys matching the header values (e.g., 'record_id', 'title', 'create').
     *
     * @return string An HTML table containing the records.
     */
    private function displayRecords(array $header, array $records): string
    {
        // Limit to just the most recent few tokens
        if (isset($_SESSION['page_tokens']) && count($_SESSION['page_tokens']) > 5) {
            asort($_SESSION['page_tokens']);
            $_SESSION['page_tokens'] = array_slice($_SESSION['page_tokens'], -5, 5, true);
        }
        $pageToken = $this->generatePageToken();

        $html = '<br /><table class="default-table">';

        // Add the header row
        $html .= '<thead><tr>';
        $html .= '<th> --crud-- </th>';
        foreach ($header as $key => $column) {
            $html .= '<th>' . HtmlHelper::escape($column) . '</th>';
        }
        $html .= '</tr></thead>';

        // Add the record rows
        $html .= '<tbody>';
        foreach ($records as $record) {
            $html .= '<tr>';
            $html .= "<td>
                <a href=\"/users/edit/{$record["user_id"]}\">Edit</a>
                <a href=\"/users/show/{$record["user_id"]}\">Viewfook</a>
                <a href=\"/users/delete/{$record["user_id"]}?token={$pageToken}\">Deletefook1</a></td>";
            foreach ($header as $key => $column) {
                $value = (string)($record[$key] ?? ''); // Use null coalescing operator to handle missing keys

                // THIS IS WHERE YOUR FORMATTER IS APPLIED
                if (isset($columnDef['formatter']) && is_callable($columnDef['formatter'])) {
                    $formattedValue = $columnDef['formatter']($value);
                } else {
                    $formattedValue = htmlspecialchars((string)$value);
                }

                $html .= '<td>' . HtmlHelper::escape($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }



    private function generatePageToken()
    {
        $token = bin2hex(random_bytes(16)); // Smaller token size is fine here

        // Initialize token storage if not exists
        if (!isset($_SESSION['page_tokens'])) {
            $_SESSION['page_tokens'] = [];
        }

        // Store token with timestamp
        $_SESSION['page_tokens'][$token] = time();

        // Clean up old tokens (keep last 10)
        if (count($_SESSION['page_tokens']) > 10) {
            // Sort by timestamp (oldest first)
            asort($_SESSION['page_tokens']);
            // Remove oldest
            array_shift($_SESSION['page_tokens']);
        }

        return $token;
    }
}
