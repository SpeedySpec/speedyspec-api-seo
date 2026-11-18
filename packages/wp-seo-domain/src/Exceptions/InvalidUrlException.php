<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Exceptions;

use InvalidArgumentException;

/**
 * Signals that a value is not an absolute http or https URL.
 *
 * @since 0.1.0
 */
class InvalidUrlException extends InvalidArgumentException
{
}
