<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\EventDispatcherInterface;

/**
 * Routes SEO events and filters through the WordPress hook system so plugins can use add_action and add_filter.
 *
 * Events become actions named after their class, so listeners subscribe with the fully qualified class name.
 *
 * @since 0.1.0
 */
class WordPressEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): void
    {
        do_action($event::class, $event);
    }

    public function filter(string $name, mixed $value, mixed ...$arguments): mixed
    {
        return apply_filters($name, $value, ...$arguments);
    }
}
