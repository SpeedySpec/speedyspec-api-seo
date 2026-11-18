<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

/**
 * Represents a single link element of the document head, independent of how it is rendered.
 *
 * @since 0.1.0
 */
class LinkTag
{
    /**
     * @param string $rel
     *   The relationship, for example canonical.
     * @param string $href
     *   The unescaped target URL.
     * @param array<string, string> $attributes
     *   Additional attributes keyed by name, for example hreflang.
     */
    public function __construct(
        public readonly string $rel,
        public readonly string $href,
        public readonly array $attributes = [],
    ) {
    }
}
