<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Exceptions;

use RuntimeException;

/**
 * Signals that a contract was requested or extended before any implementation package bound it.
 *
 * @since 0.1.0
 */
class ServiceNotRegisteredException extends RuntimeException
{
}
