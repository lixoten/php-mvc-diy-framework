<?php

declare(strict_types=1);

namespace Core\View\Renderer;

use Core\Form\Field\FieldInterface;
use Core\I18n\I18nTranslator;
use Core\View\ViewInterface;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Bootstrap 5 View renderer.
 *
 * This class provides a concrete implementation for rendering View objects
 * into Bootstrap 5 compatible HTML, following the external rendering pattern.
 * It leverages AbstractViewRenderer for common logic and ThemeServiceInterface
 * for Bootstrap-specific class names and icons.
 */
class BootstrapViewRenderer extends AbstractViewRenderer
{
    /**
     * Constructor.
     *
     * @param ThemeServiceInterface $themeService
     * @param I18nTranslator $translator
     * @param FormatterService $formatterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ThemeServiceInterface $themeService,
        I18nTranslator $translator,
        FormatterService $formatterService,
        LoggerInterface $logger
    ) {
        parent::__construct($themeService, $translator, $formatterService, $logger);

        // Override default options if needed specifically for Bootstrap
        $this->defaultOptions['layout_type'] = 'sections'; // Bootstrap often uses sections
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHeader(ViewInterface $view, array $options): string
    {
        $output = '';

        $cardHeaderClass = $this->themeService->getElementClass('card.header');
        $output .= '<div class="' . htmlspecialchars($cardHeaderClass) . '">';

        // Get title from options, fallback to view title, then page name
        $title = $options['title'] ?? $view->getTitle();
        $title = $this->translator->get($title, pageName: $view->getPageName());

        $headingLevel = $options['view_heading_level'] ?? 'h2'; // Default to h2
        // Validate heading level to prevent XSS and ensure valid HTML
        $validHeadingLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (!in_array($headingLevel, $validHeadingLevels, true)) {
            $this->logger->warning('Invalid heading level provided for view header. Defaulting to h2.', ['level' => $headingLevel]);
            $headingLevel = 'h2';
        }

        $headingClass = $this->themeService->getElementClass('view.heading');

        $output .= "<{$headingLevel} class=\"" . htmlspecialchars($headingClass) . "\">" .
                   htmlspecialchars($title) .
                   "</{$headingLevel}>";

        $output .= '</div>'; // Close card.header

        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * Renders the main content of the view, organizing fields based on the layout configuration.
     */
    protected function renderBodyContent(ViewInterface $view, array $options): string
    {
        $output = '';
        $layout = $view->getLayout();
        $layoutType = $options['layout_type'] ?? 'sections';

        // If no layout is defined, render all fields sequentially without specific structure
        if (empty($layout)) {
            $output .= '<div class="row">';
            foreach ($view->getFields() as $field) {
                $output .= '<div class="col-md-6">'; // Default to two columns for individual fields
                $output .= $this->renderField($view, $field, $options);
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }

        switch ($layoutType) {
            case 'sections':
            case 'fieldsets': // Can treat fieldsets similarly for now, focusing on sections primarily
                foreach ($layout as $section) {
                    $output .= $this->renderLayoutSection($view, $section, $options);
                }
                break;
            case 'single_column':
            default:
                // Render all fields in a single column within a row structure
                $output .= '<div class="row">';
                foreach ($view->getFields() as $field) {
                    $output .= '<div class="col-12">'; // Full width column for each field
                    $output .= $this->renderField($view, $field, $options);
                    $output .= '</div>';
                }
                $output .= '</div>';
                break;
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * Renders a specific layout section within the View, which typically contains a group of fields.
     */
    public function renderLayoutSection(ViewInterface $view, array $section, array $options = []): string
    {
        $output = '';
        $pageName = $view->getPageName();
        $sectionTitle = $section['title'] ?? null;
        $sectionId = $section['id'] ?? null;
        $sectionFields = $section['fields'] ?? [];
        $sectionClass = $section['class'] ?? $this->themeService->getElementClass('view.section');

        $output .= '<div';
        if ($sectionId !== null) {
            $output .= ' id="' . htmlspecialchars($sectionId) . '"';
        }
        $output .= ' class="' . htmlspecialchars($sectionClass) . '">';

        if ($sectionTitle !== null) {
            $titleTranslated = $this->translator->get($sectionTitle, pageName: $pageName);
            $output .= '<h5 class="' . htmlspecialchars($this->themeService->getElementClass('view.section.title')) . '">' .
                       htmlspecialchars($titleTranslated) .
                       '</h5>';
        }

        // Determine column structure for fields within this section
        $numColumns = $section['columns'] ?? 1; // Default to 1 column
        $colClass = 'col-md-' . (12 / $numColumns);

        $output .= '<div class="row">';
        foreach ($sectionFields as $fieldName) {
            try {
                $field = $view->getField($fieldName);
                $output .= '<div class="' . htmlspecialchars($colClass) . '">';
                $output .= $this->renderField($view, $field, $options);
                $output .= '</div>';
            } catch (\OutOfBoundsException $e) {
                $this->logger->warning('Field not found for view layout section.', [
                    'fieldName' => $fieldName,
                    'pageKey' => $view->getPageKey(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
        $output .= '</div>'; // Close row

        $output .= '</div>'; // Close section container

        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * Renders a single field for display within the View.
     */
    public function renderField(ViewInterface $view, FieldInterface $field, array $options = []): string
    {
        $output = '';
        $fieldName = $field->getName();
        $fieldValue = $view->getData()[$fieldName] ?? null; // Get actual data value
        $fieldOptions = $field->getOptions();
        $fieldLabel = $this->translator->get($field->getLabel(), pageName: $view->getPageName());
        $recordData = $view->getData(); // Pass full record for formatters like 'options_provider'

        $wrapperClass = $options['field_wrapper_class'] ?? $this->themeService->getElementClass('view.field.wrapper');
        $labelClass = $options['field_label_class'] ?? $this->themeService->getElementClass('view.field.label');
        $valueClass = $options['field_value_class'] ?? $this->themeService->getElementClass('view.field.value');

        $output .= '<div class="' . htmlspecialchars($wrapperClass) . '">';
        $output .= '<div class="' . htmlspecialchars($labelClass) . '">' . htmlspecialchars($fieldLabel) . '</div>';
        $output .= '<div class="' . htmlspecialchars($valueClass) . '">';

        // Delegate value formatting to AbstractViewRenderer's renderValue method
        $formattedValue = $this->renderValue($fieldName, $fieldValue, $recordData, $fieldOptions);

        $output .= (string)$formattedValue; // Cast to string as renderValue returns string
        $output .= '</div>'; // Close field-value
        $output .= '</div>'; // Close field-wrapper

        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * Renders action buttons for the view, such as "Edit", "Delete", and "Back".
     */
    protected function renderActions(ViewInterface $view, array $options): string
    {
        if (!($options['show_actions'] ?? true)) {
            return '';
        }

        $output = '';
        $pageName = $view->getPageName();

        $cardFooterClass = $this->themeService->getElementClass('card.footer');
        $buttonGroupClass = $this->themeService->getElementClass('button.group');

        $output .= '<div class="' . htmlspecialchars($cardFooterClass) . '">';
        $output .= '<div class="' . htmlspecialchars($buttonGroupClass) . '">';

        // Edit Button
        if (($options['show_action_edit'] ?? true) && !empty($options['edit_url'])) {
            $editButtonLabel = $this->translator->get(
                $options['edit_button_label'] ?? 'common.button.edit',
                pageName: $pageName
            );
            $editButtonClass = $this->getActionButtonClass('edit');
            $output .= '<a href="' . htmlspecialchars($options['edit_url']) . '" class="' . htmlspecialchars($editButtonClass) . '">';
            $output .= $this->themeService->getIconHtml('edit') . ' ' . htmlspecialchars($editButtonLabel);
            $output .= '</a>';
        }

        // Delete Button (can be a button triggering a modal)
        if (($options['show_action_delete'] ?? true) && !empty($options['delete_url'])) {
            $deleteButtonLabel = $this->translator->get(
                $options['delete_button_label'] ?? 'common.button.delete',
                pageName: $pageName
            );
            $deleteButtonClass = $this->getActionButtonClass('delete');
            // Assuming a simple link for now, or add modal data attributes as needed
            $output .= '<a href="' . htmlspecialchars($options['delete_url']) . '" class="' . htmlspecialchars($deleteButtonClass) . ' ms-2">';
            $output .= $this->themeService->getIconHtml('delete') . ' ' . htmlspecialchars($deleteButtonLabel);
            $output .= '</a>';
        }

        // Back Button
        if (!empty($options['back_url'])) {
            $backButtonLabel = $this->translator->get(
                $options['back_button_label'] ?? 'common.button.back',
                pageName: $pageName
            );
            $backButtonClass = $this->getActionButtonClass('back');
            $output .= '<a href="' . htmlspecialchars($options['back_url']) . '" class="' . htmlspecialchars($backButtonClass) . ' ms-2">';
            $output .= $this->themeService->getIconHtml('back') . ' ' . htmlspecialchars($backButtonLabel);
            $output .= '</a>';
        }

        $output .= '</div>'; // Close button.group
        $output .= '</div>'; // Close card.footer

        return $output;
    }
}
