<?php

declare(strict_types=1);

namespace Core\Form\Event;

/**
 * Form event constants
 */
final class FormEvents
{
    /**
     * Triggered before form data is set
     */
    public const PRE_SET_DATA = 'form.pre_set_data';

    /**
     * Triggered after form data is set
     */
    public const POST_SET_DATA = 'form.post_set_data';

    /**
     * Triggered before form submission is processed
     */
    public const PRE_SUBMIT = 'form.pre_submit';

    /**
     * Triggered after form submission is processed
     */
    public const POST_SUBMIT = 'form.post_submit';

    /**
     * Triggered before form validation
     */
    public const PRE_VALIDATE = 'form.pre_validate';

    /**
     * Triggered after form validation
     */
    public const POST_VALIDATE = 'form.post_validate';
}
