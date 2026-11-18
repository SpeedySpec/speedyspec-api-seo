<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Exceptions;

use InvalidArgumentException;

/**
 * Signals that a robots directive name cannot be emitted in a robots meta tag or header.
 *
 * @since 0.1.0
 */
class InvalidRobotsDirectiveException extends InvalidArgumentException
{
}
