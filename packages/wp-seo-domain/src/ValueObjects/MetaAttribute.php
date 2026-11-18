<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

/**
 * Names the HTML attribute that identifies a meta tag, since Open Graph uses property while most tags use name.
 *
 * @since 0.1.0
 */
enum MetaAttribute: string
{
    case Name = 'name';
    case Property = 'property';
}
