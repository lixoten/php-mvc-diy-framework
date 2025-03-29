<?php

declare(strict_types=1);

namespace Core\Event;

use Core\Form\Event\FormEventInterface;
use Core\Form\Event\FormEventSubscriberInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Simple event dispatcher implementation
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array
     */
    private array $listeners = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch(object $event)
    {
        if (!$event instanceof FormEventInterface) {
            return $event;
        }

        $eventName = $event->getName();

        if (!isset($this->listeners[$eventName])) {
            return $event;
        }

        foreach ($this->listeners[$eventName] as $priorityListeners) {
            foreach ($priorityListeners as $listener) {
                $listener($event);

                // If propagation is stopped, break out
                if (method_exists($event, 'isPropagationStopped') && $event->isPropagationStopped()) {
                    break 2;
                }
            }
        }

        return $event;
    }

    /**
     * Add an event listener
     *
     * @param string $eventName
     * @param callable $listener
     * @param int $priority Higher priority executes earlier
     * @return self
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): self
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        if (!isset($this->listeners[$eventName][$priority])) {
            $this->listeners[$eventName][$priority] = [];
        }

        $this->listeners[$eventName][$priority][] = $listener;

        // Sort by priority (higher number = higher priority)
        krsort($this->listeners[$eventName]);

        return $this;
    }

    /**
     * Add an event subscriber
     *
     * @param FormEventSubscriberInterface $subscriber
     * @return self
     */
    public function addSubscriber(FormEventSubscriberInterface $subscriber): self
    {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
            } elseif (is_array($params)) {
                $this->addListener(
                    $eventName,
                    [$subscriber, $params[0]],
                    $params[1] ?? 0
                );
            }
        }

        return $this;
    }
}
