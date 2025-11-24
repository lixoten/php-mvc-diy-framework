<?php

declare(strict_types=1);

namespace Core\View\Renderer;

/**
 * Registry for View renderers.
 *
 * This class manages the available renderers for View objects.
 * Each renderer is responsible for generating HTML markup according
 * to a specific CSS framework (Bootstrap, Tailwind, etc.).
 */
class ViewRendererRegistry
{
    /**
     * Registered renderers.
     *
     * @var array<string, ViewRendererInterface>
     */
    private array $renderers = [];

    /**
     * Default renderer name.
     *
     * @var string
     */
    private string $defaultRenderer = 'bootstrap';

    /**
     * Register a renderer with a given name.
     *
     * @param string $name The unique name for the renderer (e.g., 'bootstrap').
     * @param ViewRendererInterface $renderer The renderer instance.
     * @return self
     */
    public function register(string $name, ViewRendererInterface $renderer): self
    {
        $this->renderers[$name] = $renderer;
        return $this;
    }

    /**
     * Get a renderer by name.
     *
     * If no name is provided or the name is empty, the default renderer will be returned.
     *
     * @param string|null $name The name of the renderer to retrieve.
     * @return ViewRendererInterface
     * @throws \InvalidArgumentException If the renderer with the given name is not found.
     */
    public function getRenderer(?string $name = null): ViewRendererInterface
    {
        // Handle both null and empty string by using the default renderer name.
        $name = ($name === null || trim($name) === '') ? $this->defaultRenderer : $name;

        if (!isset($this->renderers[$name])) {
            throw new \InvalidArgumentException("View renderer \"{$name}\" not found");
        }

        return $this->renderers[$name];
    }

    /**
     * Set the default renderer by its registered name.
     *
     * @param string $name The name of the renderer to set as default.
     * @return self
     * @throws \InvalidArgumentException If the renderer is not registered.
     */
    public function setDefaultRenderer(string $name): self
    {
        if (!isset($this->renderers[$name])) {
            throw new \InvalidArgumentException("Cannot set default View renderer to \"{$name}\": renderer not registered");
        }

        $this->defaultRenderer = $name;
        return $this;
    }

    /**
     * Get the name of the currently set default renderer.
     *
     * @return string
     */
    public function getDefaultRenderer(): string
    {
        return $this->defaultRenderer;
    }

    /**
     * Check if a renderer with the given name is registered.
     *
     * @param string $name The name of the renderer to check.
     * @return bool True if the renderer is registered, false otherwise.
     */
    public function hasRenderer(string $name): bool
    {
        return isset($this->renderers[$name]);
    }

    /**
     * Get all registered renderers.
     *
     * @return array<string, ViewRendererInterface> An associative array of registered renderers.
     */
    public function getRenderers(): array
    {
        return $this->renderers;
    }
}
