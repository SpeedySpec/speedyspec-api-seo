<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Decides the document title of a page, applying overrides, content and site fallbacks.
 *
 * @since 0.1.0
 */
interface TitleResolverInterface
{
    /**
     * Resolves the document title of the page.
     *
     * Resolving is deterministic: equal contexts give equal results.
     *
     * @param PageContext $context
     *   The page being rendered.
     * @return string
     *   The title as display text; never null, so a head document always has something to show. It may already
     *   contain HTML entities but is never trusted as markup, so renderers escape it without double-encoding.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): string;
}
