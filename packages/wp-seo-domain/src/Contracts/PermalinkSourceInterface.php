<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Looks up the public address of a page so the domain can build canonical and social URLs without a router.
 *
 * @since 0.1.0
 */
interface PermalinkSourceInterface
{
    /**
     * Finds the public URL of the page.
     *
     * @param PageContext $context
     *   The page whose address is wanted.
     * @return Url|null
     *   The URL, or null when the page has no public address, for example a missing page.
     *
     * @since 0.1.0
     */
    public function permalinkFor(PageContext $context): ?Url;
}
