<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\Context\CurrentContext;
use Core\I18n\I18nTranslator;
use Core\List\ListInterface; // Still needed for docblocks, though renderBody is removed
use Core\List\ListView;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Material Design list renderer
 *
 * This class extends AbstractListRenderer and overrides only methods that
 * require specific Material Design HTML structure or JavaScript behavior
 * that cannot be fully abstracted by ThemeService. All CSS classes
 * are fetched from ThemeService to maintain framework neutrality.
 */
class MaterialListRenderer extends AbstractListRenderer
{
    /**
     * Constructor
     *
     * @param ThemeServiceInterface $themeService The theme service
     * @param I18nTranslator $translator The translator service
     * @param FormatterService $formatterService The formatter service
     * @param LoggerInterface $logger The logger service
     * @param ContainerInterface $container The DI container
     */
    public function __construct(
        ThemeServiceInterface $themeService,
        protected I18nTranslator $translator,
        FormatterService $formatterService,
        LoggerInterface $logger,
        ContainerInterface $container,
        protected CurrentContext $currentContext
    ) {
        parent::__construct(
            $themeService,
            $translator,
            $formatterService,
            $logger,
            $container,
            $currentContext
        );

        // Material Design-specific default options.
        // These are just parameters for the abstract renderer to use,
        // and ThemeService provides the actual CSS classes.
        $this->defaultOptions = array_merge($this->defaultOptions, [
            'view_type'  => self::VIEW_GRID, // Default to grid for Material
            'elevation'  => 2,               // Material elevation level (0-24)
            'card_shape' => 'rounded',       // rounded, rounded-lg, or sharp (mapped by ThemeService)
            'dense'      => false,           // Use dense layout (mapped by ThemeService)
        ]);
    }

    /**
     * Render the main body content of the list
     *
     * This method delegates to the parent's logic as the view switching (table, grid, list)
     * and toggle rendering are handled in AbstractListRenderer and are framework-agnostic.
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderBody(ListInterface $list, array $options = []): string
    {
        // Delegate to the AbstractListRenderer's renderBody method,
        // which contains the framework-agnostic view type switching and toggle logic.
        return parent::renderBody($list, $options);
    }

    /**
     * Renders a delete confirmation modal with Material Design styling and behavior.
     *
     * This method contains Material Design-specific HTML structure and JavaScript
     * to ensure the modal functions correctly within an MDC (Material Design Components) context.
     * All CSS classes and data attributes are fetched from the ThemeService.
     *
     * @param ListView $list The list view object, providing actions and CSRF token.
     * @return string The HTML for the Material Design delete confirmation modal.
     */
    public function renderDeleteModal(ListView $list): string
    {
        if (!$list->hasActions() || !isset($list->getActions()['delete'])) {
            return '';
        }

        $options = $list->getActions()['delete'];
        $title = htmlspecialchars($options['modal_title'] ?? 'Confirm Delete');
        $formAction = htmlspecialchars($options['form_action'] ?? '');

        $csrfField = '';
        if ($list->hasCsrfProtection()) {
            $csrfField = '<input type="hidden" name="csrf_token" value="' .
                htmlspecialchars($list->getCsrfToken() ?? '') . '">';
        }

        // Fetch Material Design classes and data attributes from ThemeService
        $modalClass = $this->themeService->getElementClass('modal') ?? 'mdc-dialog';
        $modalBackdropClass = $this->themeService->getElementClass('modal.backdrop') ?? 'mdc-dialog__scrim';
        $modalContainerClass = $this->themeService->getElementClass('modal.container') ?? 'mdc-dialog__container';
        $modalSurfaceClass = $this->themeService->getElementClass('modal.surface') ?? 'mdc-dialog__surface mdc-elevation--z2 rounded-4 shadow';
        $modalHeaderClass = $this->themeService->getElementClass('modal.header') ?? 'mdc-dialog__header border-bottom-0';
        $modalTitleClass = $this->themeService->getElementClass('modal.title') ?? 'mdc-dialog__title fw-bold';
        $modalCloseButtonClass = $this->themeService->getElementClass('modal.close_button') ?? 'mdc-icon-button material-icons mdc-dialog__close';
        $modalCloseDataAttribute = $this->themeService->getElementClass('modal.close_data_attribute') ?? 'data-mdc-dialog-action';
        $modalBodyClass = $this->themeService->getElementClass('modal.body') ?? 'mdc-dialog__content py-3';
        $modalBodyIconWrapperClass = $this->themeService->getElementClass('modal.body.icon_wrapper') ?? 'd-flex text-danger me-3';
        $modalBodyIconClass = $this->themeService->getIconHtml('warning_triangle') ?? '<i class="fas fa-exclamation-triangle fa-2x"></i>'; // Get icon HTML directly
        $modalFooterClass = $this->themeService->getElementClass('modal.footer') ?? 'mdc-dialog__actions flex-column border-top-0';
        $cancelButtonClass = $this->themeService->getElementClass('button.cancel') ?? 'mdc-button mdc-button--text mdc-ripple-surface w-100';
        $deleteButtonClass = $this->themeService->getElementClass('button.delete_modal') ?? 'mdc-button mdc-button--raised mdc-button--danger mdc-ripple-surface w-100';
        $deleteButtonTriggerClass = $this->themeService->getElementClass('button.delete_trigger') ?? 'mdc-list-item__meta--delete-trigger'; // Class for buttons that open this modal

        // Translate labels for buttons and confirmation message
        $cancelLabel = htmlspecialchars($this->translator->get('button.cancel', pageName: $list->getPageName()));
        $deleteLabel = htmlspecialchars($this->translator->get('button.delete', pageName: $list->getPageName()));
        $confirmMessage = htmlspecialchars($this->translator->get('list.modal.confirm_delete', pageName: $list->getPageName()));


        return <<<HTML
        <div class="{$modalClass}" id="deleteItemModal" tabindex="-1" aria-hidden="true" style="visibility: hidden;">
            <div class="{$modalContainerClass}">
                <div class="{$modalSurfaceClass}">
                    <div class="{$modalHeaderClass}">
                        <h5 class="{$modalTitleClass}">{$title}</h5>
                        <button type="button" class="{$modalCloseButtonClass}" {$modalCloseDataAttribute}="close" aria-label="Close"></button>
                    </div>
                    <form id="deleteItemForm" method="POST" action="{$formAction}">
                        <div class="{$modalBodyClass}">
                            <div class="{$modalBodyIconWrapperClass}">
                                {$modalBodyIconClass}
                                <p class="mb-0">{$confirmMessage}</p>
                            </div>
                            <input type="hidden" name="id" id="deleteItemId">
                            {$csrfField}
                        </div>
                        <div class="{$modalFooterClass}">
                            <button type="submit" class="{$deleteButtonClass}">{$deleteLabel}</button>
                            <button type="button" class="{$cancelButtonClass}" {$modalCloseDataAttribute}="close">{$cancelLabel}</button>
                        </div>
                   </form>
                </div>
            </div>
            <div class="{$modalBackdropClass}"></div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('deleteItemModal');
                if (!modal) return; // Ensure the modal element exists

                // Use dynamic selectors and attributes fetched from ThemeService
                const closeButtons = modal.querySelectorAll('[{$modalCloseDataAttribute}="close"]');
                const deleteButtons = document.querySelectorAll('.{$deleteButtonTriggerClass}');
                const idInput = document.getElementById('deleteItemId');
                const modalBody = modal.querySelector('.{$modalBodyClass} p');

                // Function to show the modal
                function showModal() {
                    modal.style.visibility = 'visible';
                    modal.setAttribute('aria-hidden', 'false');
                    modal.focus(); // Focus modal for accessibility
                }

                // Function to hide the modal
                function hideModal() {
                    modal.style.visibility = 'hidden';
                    modal.setAttribute('aria-hidden', 'true');
                }

                // Setup delete buttons to open the modal and populate data
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function(event) {
                        event.preventDefault(); // Prevent default link/button action
                        const id = this.getAttribute('data-id');
                        const confirmMsg = this.getAttribute('data-confirm');

                        if (idInput) idInput.value = id;
                        if (modalBody && confirmMsg) modalBody.textContent = confirmMsg;

                        showModal();
                    });
                });

                // Setup close buttons
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        hideModal();
                    });
                });

                // Close when clicking outside the modal content
                modal.addEventListener('click', function(event) {
                    // Check if the click occurred on the backdrop (not inside modal-surface)
                    if (event.target.classList.contains('{$modalBackdropClass}')) {
                        hideModal();
                    }
                });

                // Close with Escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                        hideModal();
                    }
                });
            });
        </script>
        HTML;
    }
}
