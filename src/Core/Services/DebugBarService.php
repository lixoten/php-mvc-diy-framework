<?php

declare(strict_types=1);

namespace Core\Services;

use DebugBar\StandardDebugBar;

class DebugBarService
{
    private StandardDebugBar $debugBar;

    public function __construct()
    {
        $this->debugBar = new StandardDebugBar();
    }

    public function addMessage(string $message, string $label = 'info'): void
    {
        $this->debugBar['messages']->addMessage($message, $label);
    }

    public function getJavascriptRenderer()
    {
        return $this->debugBar->getJavascriptRenderer();
    }

    public function getDebugBar(): StandardDebugBar
    {
        return $this->debugBar;
    }
}