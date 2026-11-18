<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Decides the canonical address of a page so duplicate URLs consolidate on one.
 *
 * @since 0.1.0
 */
interface CanonicalUrlResolverInterface
{
    /**
     * Resolves the canonical URL of the page.
     *
     * Resolving is deterministic: equal contexts give equal results.
     *
     * @param PageContext $context
     *   The page being rendered.
     * @return Url|null
     *   The canonical URL, or null when the page should not declare one.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): ?Url;
}
