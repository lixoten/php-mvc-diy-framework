<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\Context\CurrentContext;
use Core\I18n\I18nTranslator;
use Core\List\ListInterface;
use Core\List\ListView;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Vanilla CSS list renderer - minimalist approach with pure CSS
 */
class VanillaListRenderer extends AbstractListRenderer
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

        // Vanilla CSS-specific default options, fetched from ThemeService
        // These elements should be defined in your vanilla_theme.php config.
        $this->defaultOptions = array_merge($this->defaultOptions, [
            'view_type' => self::VIEW_TABLE,
            'card_shape' => $this->themeService->getElementClass('card.shape') ?? 'rounded',
            'container_class' => $this->themeService->getElementClass('container') ?? 'vanilla-container',
            'row_class' => $this->themeService->getElementClass('row') ?? 'vanilla-row',
            'card_class' => $this->themeService->getElementClass('card') ?? 'vanilla-card',
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
     * Render delete confirmation modal with vanilla CSS
     *
     * This method uses ThemeService for all CSS classes and JavaScript selectors
     * to ensure framework neutrality, while maintaining a vanilla HTML structure.
     *
     * @param ListView $list The list view
     * @return string The HTML for the delete confirmation modal
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
                htmlspecialchars($list->getCsrfToken()) . '">';
        }

        // Fetch classes and attributes from ThemeService
        // These should be defined in your vanilla_theme.php config.
        $modalClass = $this->themeService->getElementClass('modal') ?? 'vanilla-modal';
        $modalContentClass = $this->themeService->getElementClass('modal.content') ?? 'vanilla-modal-content';
        $modalHeaderClass = $this->themeService->getElementClass('modal.header') ?? 'vanilla-modal-header';
        $modalCloseButtonClass = $this->themeService->getElementClass('modal.close_button') ?? 'vanilla-modal-close';
        $modalCloseDataAttribute = $this->themeService->getElementClass('modal.close_data_attribute') ?? 'data-dismiss'; // Default if not in ThemeService config
        $modalBodyClass = $this->themeService->getElementClass('modal.body') ?? 'vanilla-modal-body';
        $modalFooterClass = $this->themeService->getElementClass('modal.footer') ?? 'vanilla-modal-footer';
        $buttonClass = $this->themeService->getElementClass('button') ?? 'vanilla-button';
        $buttonDangerClass = $this->themeService->getElementClass('button.danger') ?? 'vanilla-button-danger';
        $deleteButtonTriggerClass = $this->themeService->getElementClass('button.delete_trigger') ?? 'delete-item-btn'; // Class for buttons that open this modal

        // Translate labels for buttons and confirmation message
        $cancelLabel = htmlspecialchars($this->translator->get('button.cancel', pageName: $list->getPageName()));
        $deleteLabel = htmlspecialchars($this->translator->get('button.delete', pageName: $list->getPageName()));
        $confirmMessage = htmlspecialchars($this->translator->get('list.modal.confirm_delete', pageName: $list->getPageName()));

        $html = <<<HTML
        <div class="{$modalClass}" id="deleteItemModal" style="display: none;">
            <div class="{$modalContentClass}">
                <div class="{$modalHeaderClass}">
                    <h3>{$title}</h3>
                    <button type="button" class="{$modalCloseButtonClass}" {$modalCloseDataAttribute}="modal">&times;</button>
                </div>
                <form id="deleteItemForm" method="POST" action="{$formAction}">
                    <div class="{$modalBodyClass}">
                        <p>{$confirmMessage}</p>
                        <input type="hidden" name="id" id="deleteItemId">
                        {$csrfField}
                    </div>
                    <div class="{$modalFooterClass}">
                        <button type="button" class="{$buttonClass}" {$modalCloseDataAttribute}="modal">{$cancelLabel}</button>
                        <button type="submit" class="{$buttonClass} {$buttonDangerClass}">{$deleteLabel}</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            // Simple vanilla JavaScript for modal functionality
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('deleteItemModal');
                if (!modal) return; // Ensure the modal element exists

                // Use dynamic selectors fetched from ThemeService
                const closeButtons = modal.querySelectorAll('[{$modalCloseDataAttribute}="modal"]');
                const deleteButtons = document.querySelectorAll('.{$deleteButtonTriggerClass}');
                const idInput = document.getElementById('deleteItemId');

                // Setup delete buttons to open the modal and populate data
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const confirmMsg = this.getAttribute('data-confirm'); // Get confirmation message from button's data attribute

                        if (idInput) idInput.value = id;

                        // Use dynamic class for modal body selector
                        const msgElement = modal.querySelector('.{$modalBodyClass} p');
                        if (msgElement && confirmMsg) {
                            msgElement.textContent = confirmMsg; // Set the dynamic confirmation message
                        }

                        modal.style.display = 'block'; // Show the modal
                    });
                });

                // Setup close buttons
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        modal.style.display = 'none'; // Hide the modal
                    });
                });

                // Close when clicking outside the modal
                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none'; // Hide the modal
                    }
                });
            });
        </script>
        HTML;

        return $html;
    }
}
