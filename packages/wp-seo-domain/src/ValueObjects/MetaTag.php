<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

/**
 * Represents a single meta element of the document head, independent of how it is rendered.
 *
 * @since 0.1.0
 */
class MetaTag
{
    /**
     * @param MetaAttribute $attribute
     *   The attribute that holds the key.
     * @param string $key
     *   The tag name or property, for example description or og:title.
     * @param string $content
     *   The unescaped content value.
     */
    public function __construct(
        public readonly MetaAttribute $attribute,
        public readonly string $key,
        public readonly string $content,
    ) {
    }
}
