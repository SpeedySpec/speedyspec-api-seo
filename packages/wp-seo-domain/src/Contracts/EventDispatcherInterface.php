<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

/**
 * Gives the domain a framework-neutral extension point so hosts can observe events and alter values.
 *
 * @since 0.1.0
 */
interface EventDispatcherInterface
{
    /**
     * Announces that something happened; listeners cannot change the outcome.
     *
     * Dispatching an event nobody listens to is not an error.
     *
     * @param object $event
     *   The event describing what happened.
     *
     * @since 0.1.0
     */
    public function dispatch(object $event): void;

    /**
     * Passes a value through the listeners registered for the name so hosts can alter it.
     *
     * @param string $name
     *   The name of the filter.
     * @param mixed $value
     *   The value to filter.
     * @param mixed ...$arguments
     *   Extra read-only context handed to each listener after the value.
     * @return mixed
     *   The filtered value; the original value when nothing listens.
     *
     * @since 0.1.0
     */
    public function filter(string $name, mixed $value, mixed ...$arguments): mixed;
}
