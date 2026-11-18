<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\EventDispatcherInterface;

/**
 * Dispatches events and filters to callbacks held in memory, recording events so tests can assert on them.
 *
 * @since 0.1.0
 */
class InMemoryEventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, list<callable>> */
    private array $filters = [];

    /** @var array<class-string, list<callable>> */
    private array $listeners = [];

    /** @var list<object> */
    private array $dispatched = [];

    /**
     * Registers a filter callback; callbacks for one name run in registration order.
     *
     * @param string $name
     *   The filter name.
     * @param callable $callback
     *   Receives the current value and the extra arguments, and returns the new value.
     *
     * @since 0.1.0
     */
    public function addFilter(string $name, callable $callback): void
    {
        $this->filters[$name][] = $callback;
    }

    /**
     * Registers a listener for events of one class.
     *
     * @param class-string $eventClass
     *   The exact class of the events to observe.
     * @param callable $listener
     *   Receives the event.
     *
     * @since 0.1.0
     */
    public function listen(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    /**
     * Lists every event dispatched so far, oldest first, including those without listeners.
     *
     * @return list<object>
     *
     * @since 0.1.0
     */
    public function dispatched(): array
    {
        return $this->dispatched;
    }

    public function dispatch(object $event): void
    {
        $this->dispatched[] = $event;
        foreach ($this->listeners[$event::class] ?? [] as $listener) {
            $listener($event);
        }
    }

    public function filter(string $name, mixed $value, mixed ...$arguments): mixed
    {
        foreach ($this->filters[$name] ?? [] as $callback) {
            $value = $callback($value, ...$arguments);
        }

        return $value;
    }
}
