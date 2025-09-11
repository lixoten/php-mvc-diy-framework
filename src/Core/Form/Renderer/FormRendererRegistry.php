<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use App\Helpers\DebugRt;
use Core\Form\Renderer\FormRendererInterface;

/**
 * Registry for form renderers
 *
 * This class manages the available renderers for form generation.
 * Each renderer is responsible for generating HTML markup according
 * to a specific CSS framework (Bootstrap, Tailwind, etc.)
 */
class FormRendererRegistry
{
    public function __construct()
    {
        // Add a breakpoint here
        $r = 5;
    }

    /**
     * Registered renderers
     *
     * @var array<string, FormRendererInterface>
     */
    private array $renderers = [];

    /**
     * Default renderer name
     *
     * @var string
     */
    private string $defaultRenderer = 'bootstrap';

    /**
     * Register a renderer
     *
     * @param string $name
     * @param FormRendererInterface $renderer
     * @return self
     */
    public function register(string $name, FormRendererInterface $renderer): self
    {
        $this->renderers[$name] = $renderer;
        //DebugRt::j('1', '', $this->renderers);
        return $this;
    }

    /**
     * Get a renderer by namexxx
     *
     * @param string|null $name
     * @return FormRendererInterface
     * @throws \InvalidArgumentException If the renderer is not found
     */
    public function getRenderer(?string $name = null): FormRendererInterface
    {
        // Problem: Empty string is being treated differently than null
        // Fix: Handle both null and empty string by using default
        $name = ($name === null || trim($name) === '') ? $this->defaultRenderer : $name;

        if (!isset($this->renderers[$name])) {
            throw new \InvalidArgumentException("Renderer \"{$name}\" not found");
        }

        return $this->renderers[$name];
    }

    /**
     * Set the default renderer
     *
     * @param string $name
     * @return self
     * @throws \InvalidArgumentException If the renderer is not registered
     */
    public function setDefaultRenderer(string $name): self
    {
        if (!isset($this->renderers[$name])) {
            throw new \InvalidArgumentException("Cannot set default renderer to \"{$name}\": renderer not registered");
        }

        $this->defaultRenderer = $name;
        return $this;
    }

    /**
     * Get the default renderer name
     *
     * @return string
     */
    public function getDefaultRenderer(): string
    {
        return $this->defaultRenderer;
    }

    /**
     * Check if a renderer is registered
     *
     * @param string $name
     * @return bool
     */
    public function hasRenderer(string $name): bool
    {
        return isset($this->renderers[$name]);
    }

    /**
     * Get all registered renderers
     *
     * @return array<string, FormRendererInterface>
     */
    public function getRenderers(): array
    {
        return $this->renderers;
    }
}
