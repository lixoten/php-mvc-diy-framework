<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use App\Helpers\DebugRt;
use Core\List\ListInterface;
use Core\List\ListView;
use Core\Services\ThemeServiceInterface;
use Core\I18n\I18nTranslator;
use App\Enums\Url;
use Core\Services\FormatterService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Bootstrap list renderer
 */
class BootstrapListRenderer extends AbstractListRenderer
{
    /**
     * Constructor
     */
    public function __construct(
        protected ThemeServiceInterface $themeService,
        protected I18nTranslator $translator,
        protected FormatterService $formatterService,
        protected LoggerInterface $logger,
        protected ContainerInterface $container
    ) {
        parent::__construct(
            $themeService,
            $translator,
            $formatterService,
            $logger,
            $container
        );
        $this->defaultOptions['view_type'] = self::VIEW_TABLE; // Fik - Override List View Default - GRID TABLE LIST
    }


    /**
     * Render list body (table view)
     */
    public function renderBody(ListInterface $list, array $options = []): string
    {
        return $this->renderTableView($list, $options);
    }


    /**
     * Render delete modal
     *
     * @param ListInterface $list
     * @return string
     */
    // future maybe once we have js?
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

        $cancelLabel = htmlspecialchars(
            $this->translator->get('button.cancel', pageName: $list->getPageName())
        );
        $deleteLabel = htmlspecialchars(
            $this->translator->get('button.delete', pageName: $list->getPageName())
        );

        return <<<HTML
        <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{$title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="deleteItemForm" method="POST" action="{$formAction}">
                        <div class="modal-body">
                            <p>Are you sure you want to delete this item?</p>
                            <input type="hidden" name="id" id="deleteItemId">
                            {$csrfField}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <?=  $cancelLabel ?>
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <?=  $deleteLabel ?>
                            </button>
                        </div>
                   </form>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteModal = document.getElementById('deleteItemModal');
                if (deleteModal) {
                    deleteModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-id');
                        const confirmMsg = button.getAttribute('data-confirm');

                        const modalBody = deleteModal.querySelector('.modal-body p');
                        modalBody.textContent = confirmMsg;

                        const idField = document.getElementById('deleteItemId');
                        idField.value = id;
                    });
                }
            });
        </script>
        HTML;
    }

    // 642 585
}
