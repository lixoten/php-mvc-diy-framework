<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;

/**
 * Interface for form renderers
 */
interface FormRendererInterface
{
    /**
     * Render a complete form
     *
     * @param FormInterface $form
     * @param array $options
     * @return string
     */
    public function renderForm(FormInterface $form, array $options = []): string;

    /**
     * Render a single field
     *
     * @param FieldInterface $field
     * @param array $options
     * @return string
     */
    public function renderField(FieldInterface $field, array $options = []): string;

    /**
     * Render form errors
     *
     * @param FormInterface $form
     * @param array $options
     * @return string
     */
    public function renderErrors(FormInterface $form, array $options = []): string;

    /**
     * Render form start tag
     *
     * @param FormInterface $form
     * @param array $options
     * @return string
     */
    public function renderStart(FormInterface $form, array $options = []): string;

    /**
     * Render form end tag
     *
     * @param FormInterface $form
     * @param array $options
     * @return string
     */
    public function renderEnd(FormInterface $form, array $options = []): string;
}
