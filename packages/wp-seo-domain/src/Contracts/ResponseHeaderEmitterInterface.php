<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

/**
 * Sends HTTP response headers so header-based SEO signals, such as X-Robots-Tag, do not call PHP globals directly.
 *
 * @since 0.1.0
 */
interface ResponseHeaderEmitterInterface
{
    /**
     * Sends a response header.
     *
     * @param string $name
     *   The header name.
     * @param string $value
     *   The header value.
     * @param bool $replace
     *   Whether the header replaces earlier headers of the same name instead of adding another.
     *
     * @since 0.1.0
     */
    public function emit(string $name, string $value, bool $replace = true): void;
}
