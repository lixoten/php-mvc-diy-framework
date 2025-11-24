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
 * Vanilla (plain HTML/CSS) View renderer.
 *
 * This class provides a concrete implementation for rendering View objects
 * into plain HTML, leveraging AbstractViewRenderer for common logic and
 * ThemeServiceInterface for any minimal styling elements.
 */
class VanillaViewRenderer extends AbstractViewRenderer
{
    /**
     * Constructor.
     */
    public function __construct(
        ThemeServiceInterface $themeService,
        I18nTranslator $translator,
        FormatterService $formatterService,
        LoggerInterface $logger
    ) {
        parent::__construct($themeService, $translator, $formatterService, $logger);
        // Vanilla-specific overrides for defaultOptions can go here if needed
        // For vanilla, layout_type might default to 'single_column' more often
        $this->defaultOptions['layout_type'] = 'single_column';
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHeader(ViewInterface $view, array $options): string
    {
        // Vanilla specific implementation for rendering the view header
        $output = '';
        $title = $options['title'] ?? $view->getTitle();
        $title = $this->translator->get($title, pageName: $view->getPageName());
        $headingLevel = $options['view_heading_level'] ?? 'h2';
        $validHeadingLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (!in_array($headingLevel, $validHeadingLevels, true)) {
            $this->logger->warning('Invalid heading level provided for vanilla view header. Defaulting to h2.', ['level' => $headingLevel]);
            $headingLevel = 'h2';
        }
        $output .= '<div class="vanilla-view-header">';
        $output .= "<{$headingLevel}>" . htmlspecialchars($title) . "</{$headingLevel}>";
        $output .= '</div>';
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
        $layoutType = $options['layout_type'] ?? 'single_column';

        if (empty($layout)) {
            $output .= '<div class="vanilla-view-fields-container">';
            foreach ($view->getFields() as $field) {
                $output .= $this->renderField($view, $field, $options);
            }
            $output .= '</div>';
            return $output;
        }

        switch ($layoutType) {
            case 'sections':
            case 'fieldsets':
                foreach ($layout as $section) {
                    $output .= $this->renderLayoutSection($view, $section, $options);
                }
                break;
            case 'single_column':
            default:
                $output .= '<div class="vanilla-view-fields-container">';
                foreach ($view->getFields() as $field) {
                    $output .= $this->renderField($view, $field, $options);
                }
                $output .= '</div>';
                break;
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * Renders a specific layout section within the View.
     */
    public function renderLayoutSection(ViewInterface $view, array $section, array $options = []): string
    {
        $output = '';
        $pageName = $view->getPageName();
        $sectionTitle = $section['title'] ?? null;
        $sectionFields = $section['fields'] ?? [];

        $output .= '<div class="vanilla-view-section">';
        if ($sectionTitle !== null) {
            $titleTranslated = $this->translator->get($sectionTitle, pageName: $pageName);
            $output .= '<h3>' . htmlspecialchars($titleTranslated) . '</h3>';
        }

        $output .= '<div class="vanilla-view-section-fields">';
        foreach ($sectionFields as $fieldName) {
            try {
                $field = $view->getField($fieldName);
                $output .= '<div class="vanilla-layout-grid__cell">'; // Changed from mdc to vanilla
                $output .= $this->renderField($view, $field, $options);
                $output .= '</div>';
            } catch (\OutOfBoundsException $e) {
                $this->logger->warning('Field not found for Vanilla view layout section.', [
                    'fieldName' => $fieldName,
                    'pageKey' => $view->getPageKey(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
        $output .= '</div>';
        $output .= '</div>';
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
        $fieldValue = $view->getData()[$fieldName] ?? null;
        $fieldOptions = $field->getOptions();
        $fieldLabel = $this->translator->get($field->getLabel(), pageName: $view->getPageName());
        $recordData = $view->getData();

        $output .= '<div class="vanilla-field-wrapper">';
        $output .= '<strong class="vanilla-field-label">' . htmlspecialchars($fieldLabel) . ':</strong> ';
        $output .= '<span class="vanilla-field-value">';
        $formattedValue = $this->renderValue($fieldName, $fieldValue, $recordData, $fieldOptions);
        $output .= (string)$formattedValue;
        $output .= '</span>';
        $output .= '</div>';
        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * Renders action buttons for the view.
     */
    protected function renderActions(ViewInterface $view, array $options): string
    {
        if (!($options['show_actions'] ?? true)) {
            return '';
        }
        $output = '';
        $pageName = $view->getPageName();
        $output .= '<div class="vanilla-view-actions">';
        if (($options['show_action_edit'] ?? true) && !empty($options['edit_url'])) {
            $editButtonLabel = $this->translator->get(
                $options['edit_button_label'] ?? 'common.button.edit',
                pageName: $pageName
            );
            $output .= '<a href="' . htmlspecialchars($options['edit_url']) . '" class="vanilla-button vanilla-button--primary">' . htmlspecialchars($editButtonLabel) . '</a>';
        }
        if (($options['show_action_delete'] ?? true) && !empty($options['delete_url'])) {
            $deleteButtonLabel = $this->translator->get(
                $options['delete_button_label'] ?? 'common.button.delete',
                pageName: $pageName
            );
            $output .= '<a href="' . htmlspecialchars($options['delete_url']) . '" class="vanilla-button vanilla-button--danger">' . htmlspecialchars($deleteButtonLabel) . '</a>';
        }
        if (!empty($options['back_url'])) {
            $backButtonLabel = $this->translator->get(
                $options['back_button_label'] ?? 'common.button.back',
                pageName: $pageName
            );
            $output .= '<a href="' . htmlspecialchars($options['back_url']) . '" class="vanilla-button">' . htmlspecialchars($backButtonLabel) . '</a>';
        }
        $output .= '</div>';
        return $output;
    }
}