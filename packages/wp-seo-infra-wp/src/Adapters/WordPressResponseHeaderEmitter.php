<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\ResponseHeaderEmitterInterface;

/**
 * Sends HTTP response headers, staying silent once output has started so a late call never raises a warning.
 *
 * @since 0.1.0
 */
class WordPressResponseHeaderEmitter implements ResponseHeaderEmitterInterface
{
    public function emit(string $name, string $value, bool $replace = true): void
    {
        if (!headers_sent()) {
            header($name . ': ' . $value, $replace);
        }
    }
}
