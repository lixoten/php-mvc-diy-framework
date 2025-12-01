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
 * Material Design View renderer.
 *
 * This class provides a concrete implementation for rendering View objects
 * into Material Design compatible HTML, leveraging AbstractViewRenderer
 * for common logic and ThemeServiceInterface for Material-specific class names and icons.
 */
class MaterialViewRenderer extends AbstractViewRenderer
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
        // Material-specific overrides for defaultOptions can go here if needed
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHeader(ViewInterface $view, array $options): string
    {
        // Material Design specific implementation for rendering the view header
        // This is a placeholder, adapt as needed for your Material theme.
        $output = '';
        $title = $options['title'] ?? $view->getTitle();
        $title = $this->translator->get($title, pageName: $view->getPageName());
        $output .= '<div class="mdc-card__header">';
        $output .= '<h2 class="mdc-typography--headline6">' . htmlspecialchars($title) . '</h2>';
        $output .= '</div>';
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderBodyContent(ViewInterface $view, array $options): string
    {
        // Material Design specific implementation for rendering the view body content
        // This is a placeholder, adapt as needed.
        $output = '';
        $output .= '<div class="mdc-card__content">';
        foreach ($view->getFields() as $field) {
            $output .= $this->renderField($view, $field, $options);
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderActions(ViewInterface $view, array $options): string
    {
        if (!($options['show_actions'] ?? true)) {
            return '';
        }
        // Material Design specific implementation for rendering action buttons
        $output = '';
        $pageName = $view->getPageName();
        $output .= '<div class="mdc-card__actions">';
        if (($options['show_action_edit'] ?? true) && !empty($options['edit_url'])) {
            $editButtonLabel = $this->translator->get(
                $options['edit_button_label'] ?? 'common.button.edit',
                pageName: $pageName
            );
            $output .= '<a href="' . htmlspecialchars($options['edit_url']) . '" class="mdc-button mdc-card__action">';
            $output .= '<span class="mdc-button__label">' . htmlspecialchars($editButtonLabel) . '</span>';
            $output .= '</a>';
        }
        if (($options['show_action_delete'] ?? true) && !empty($options['delete_url'])) {
            $deleteButtonLabel = $this->translator->get(
                $options['delete_button_label'] ?? 'common.button.delete',
                pageName: $pageName
            );
            $output .= '<a href="' . htmlspecialchars($options['delete_url']) . '" class="mdc-button mdc-card__action mdc-button--danger">';
            $output .= '<span class="mdc-button__label">' . htmlspecialchars($deleteButtonLabel) . '</span>';
            $output .= '</a>';
        }
        if (!empty($options['back_url'])) {
            $backButtonLabel = $this->translator->get(
                $options['back_button_label'] ?? 'common.button.back',
                pageName: $pageName
            );
            $output .= '<a href="' . htmlspecialchars($options['back_url']) . '" class="mdc-button mdc-card__action">';
            $output .= '<span class="mdc-button__label">' . htmlspecialchars($backButtonLabel) . '</span>';
            $output .= '</a>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderField(ViewInterface $view, FieldInterface $field, array $options = []): string
    {
        // Material Design specific implementation for rendering a single field
        $output = '';
        $fieldName = $field->getName();
        $fieldValue = $view->getData()[$fieldName] ?? null;
        $fieldOptions = $field->getOptions();
        $fieldLabel = $this->translator->get($field->getLabel(), pageName: $view->getPageName());
        $recordData = $view->getData();

        $output .= '<div class="mdc-data-table__cell">';
        $output .= '<label class="mdc-data-table__header-cell">' . htmlspecialchars($fieldLabel) . ':</label>';
        $output .= '<span class="mdc-data-table__cell-content">';
        $formattedValue = $this->renderValue($view->getPageName(), $fieldName, $fieldValue, $recordData, $fieldOptions);
        $output .= (string)$formattedValue;
        $output .= '</span>';
        $output .= '</div>';
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderLayoutSection(ViewInterface $view, array $section, array $options = []): string
    {
        // Material Design specific implementation for rendering a layout section
        $output = '';
        $pageName = $view->getPageName();
        $sectionTitle = $section['title'] ?? null;
        $sectionFields = $section['fields'] ?? [];

        $output .= '<div class="mdc-layout-grid__inner">';
        if ($sectionTitle !== null) {
            $titleTranslated = $this->translator->get($sectionTitle, pageName: $pageName);
            $output .= '<h5 class="mdc-typography--headline5">' . htmlspecialchars($titleTranslated) . '</h5>';
        }

        foreach ($sectionFields as $fieldName) {
            try {
                $field = $view->getField($fieldName);
                $output .= '<div class="mdc-layout-grid__cell">';
                $output .= $this->renderField($view, $field, $options);
                $output .= '</div>';
            } catch (\OutOfBoundsException $e) {
                $this->logger->warning('Field not found for Material view layout section.', [
                    'fieldName' => $fieldName,
                    'pageKey' => $view->getPageKey(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
        $output .= '</div>';
        return $output;
    }
}