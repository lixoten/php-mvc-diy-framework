<?php

declare(strict_types=1);

namespace App\Features\Testy\Form;

use Core\Form\FormBuilderInterface;
use Core\Form\FormTypeInterface;
use App\Helpers\DebugRt as Debug;

/**
 * Contact form type
 */
class ContactFormType implements FormTypeInterface
{
    private array $options = [];
    private ContactFieldRegistry $fieldRegistry;

     /**
     * Constructor
     *
     * @param ContactFieldRegistry $fieldRegistry
     */
    public function __construct(ContactFieldRegistry $fieldRegistry)
    {
        $this->fieldRegistry = $fieldRegistry;
    }

    // /**
    //  * Set form configuration options
    //  *
    //  * @param array $config
    //  * @return self
    //  */
    // public function setConfig(array $config): self
    // {
    //     //Debug::p(2222);
    //     $this->options = $config;
    //     return $this;
    // }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'contact_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function xxxxxbuildForm(FormBuilderInterface $builder, array $options = []): void
    {

        //Debug::p($this->options);
        // Merge options with those set via setConfig
        //$options = array_merge($this->options, $options);
        //Debug::p($options , 1);

        // Define default fields if not specified
        $fieldNames = array_keys($options['fields'] ?? [
            'name' => [],
            'email' => [],
            'subject' => [],
            'message' => []
        ]);

        //$rrr  =  $this->fieldRegistry->getName();
        //Debug::p($rrr, 1);

        // only used if NOT in $options coming in..

        //The need to be LOOKED UP against the  ContactFieldRegistry.... so if 'name' comes in, we ifnore the defaults on here, and look it up in ContactFieldRegistry and then meger it with that item.


        // Process each field
        foreach ($fieldNames as $name) {
            // 1. Get definition from registry (or empty array if not found)
            $fieldDefinition = match ($name) {
                'name' => $this->fieldRegistry->getName(),
                'email' => $this->fieldRegistry->getEmail(),
                'subject' => $this->fieldRegistry->getSubject(),
                'message' => $this->fieldRegistry->getMessage(),
                'message2' => $this->fieldRegistry->getMessage2(),
                default => []
            };

            // 2. Merge with any overrides from options
            $fieldOptions = array_merge(
                $fieldDefinition,
                $options['fields'][$name] ?? []
            );

            // 3. Add the field with combined options
            $builder->add($name, $fieldOptions);
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {

        //Debug::p($options);
        // 1. Check if layout actually contains valid sections/fieldsets
        // $hasFieldsets = isset($options['layout']['fieldsets']) && !empty($options['layout']['fieldsets']);
        // $hasSections = isset($options['layout']['sections']) && !empty($options['layout']['sections']);

        // if (
        //     !isset($options['layout_type']) ||
        //     ($options['layout_type'] !== 'fieldsets' && $options['layout_type'] !== 'sections')
        // ) {
        //     $options['layout_type'] = 'none';
        // } else {
        //     if ($options['layout_type'] === 'fieldsets') { // if no 'fieldsets' but we do have a 'sections'
        //         $options['layout_type'] = 'fieldsets';
        //     elseif ($options['layout_type'] === 'sections') { // if no 'fieldsets' but we do have a 'sections'
        //         $options['layout_type'] = 'sections';



        //     if (!$hasFieldsets && $hasSections) { // if no 'fieldsets' but we do have a 'sections'
        //         $options['layout_type'] = 'sections';
        //     } elseif (!$hasFieldsets && !$hasSections) {
        //         $options['layout_type'] = 'fieldsets'; // <<< use defaults set in generateFieldsetLayout()
        //     } else {
        //         $options['layout_type'] = 'none';
        //     }
        // }


        // Define default fields if not specified
        $fieldConfig = $options['fields'] ?? [
            'name' => [],
            'email' => [],
            'subject' => [],
            'message' => []
        ];

        // Get the field names we're working with
        $fieldNames = array_keys($fieldConfig);

        // Process each field
        foreach ($fieldNames as $name) {
            // Get definition from registry
            $fieldDef = $this->fieldRegistry->get($name) ?? [];

            // Merge with overrides from options
            $fieldOptions = array_merge($fieldDef, $fieldConfig[$name] ?? []);

            // Add field to form
            $builder->add($name, $fieldOptions);
        }

        if ($options['layout_type'] !== 'none') {
            //Debug::p($options['layout_type']);
            if (isset($options['layout']) && !empty($options['layout'][$options['layout_type']])) {
                // Use provided layout matching the detected type
                $builder->setLayout($options['layout']);
            } else {
                // Generate layout of the detected type
                $layout = $this->generateAppropriateLayout($fieldNames, $options['layout_type']);
                $builder->setLayout($layout);
            }
        }
    }


    /**
     * Generate appropriate layout based on specified type
     */
    private function generateAppropriateLayout(array $fieldNames, string $layoutType): array
    {
        //Debug::p($options['layout']);

        return match ($layoutType) {
            'fieldsets' => $this->generateFieldsetLayout($fieldNames),
            'sections' => $this->generateSectionLayout($fieldNames),
            'none', 'sequential' => $this->generateSequentialLayout($fieldNames), // Handle both terms
            default => []
        };
    }

    /**
     * Generate a fieldset-based layout
     */
    private function generateFieldsetLayout(array $fieldNames): array
    {
        // Your existing generateDefaultLayout implementation goes here
        $contactFields = array_intersect(['name', 'email', 'phone'], $fieldNames);
        $messageFields = array_intersect(['subject', 'message', 'message2'], $fieldNames);

        // Any fields not in predefined groups
        $otherFields = array_diff($fieldNames, [...$contactFields, ...$messageFields]);

        $layout = [];

        // Only create fieldsets if they have fields
        if (!empty($contactFields)) {
            $layout['fieldsets']['contact_info'] = [
                'legend' => 'Contact Information FS',
                'fields' => $contactFields
            ];
        }

        if (!empty($messageFields)) {
            $layout['fieldsets']['message_details'] = [
                'legend' => 'Your Message FS',
                'fields' => $messageFields
            ];
        }

        // Add any remaining fields to a general fieldset
        if (!empty($otherFields)) {
            $layout['fieldsets']['additional_info'] = [
                'legend' => 'Additional Information FS',
                'fields' => $otherFields
            ];
        }

        // Default to 1 column
        $layout['columns'] = 1;

        return $layout;
    }

    /**
     * Generate a section-based layout
     */
    private function generateSectionLayout(array $fieldNames): array
    {
        $layout = ['sections' => []];
        $contactFields = array_intersect(['name', 'email', 'phone'], $fieldNames);
        $messageFields = array_intersect(['subject', 'message', 'message2'], $fieldNames);
        $otherFields = array_diff($fieldNames, [...$contactFields, ...$messageFields]);

        if (!empty($contactFields)) {
            $layout['sections'][] = [
                'type' => 'header',
                'title' => 'Contact Information SECTION'
            ];
            $layout['sections'][] = [
                'type' => 'fields',
                'fields' => $contactFields
            ];
        }

        if (!empty($messageFields)) {
            if (!empty($contactFields)) {
                $layout['sections'][] = ['type' => 'divider'];
            }

            $layout['sections'][] = [
                'type' => 'header',
                'title' => 'Your Message SECTION'
            ];
            $layout['sections'][] = [
                'type' => 'fields',
                'fields' => $messageFields
            ];
        }

        if (!empty($otherFields)) {
            if (!empty($contactFields) || !empty($messageFields)) {
                $layout['sections'][] = ['type' => 'divider'];
            }

            $layout['sections'][] = [
                'type' => 'header',
                'title' => 'Additional Information SECTION'
            ];
            $layout['sections'][] = [
                'type' => 'fields',
                'fields' => $otherFields
            ];
        }

        return $layout;
    }

    /**
     * Generate a sequential layout (what we've been calling 'none')
     */
    private function generateSequentialLayout(array $fieldNames): array
    {
        return [
            'sequential' => [
                'fields' => $fieldNames
            ]
        ];
    }
}
