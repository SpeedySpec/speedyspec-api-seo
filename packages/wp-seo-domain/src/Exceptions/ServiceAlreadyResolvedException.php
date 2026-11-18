<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Exceptions;

use RuntimeException;

/**
 * Signals that a binding was changed after its instance was handed out, which would leave stale consumers behind.
 *
 * @since 0.1.0
 */
class ServiceAlreadyResolvedException extends RuntimeException
{
}
