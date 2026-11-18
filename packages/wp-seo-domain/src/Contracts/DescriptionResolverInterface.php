<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Decides the meta description of a page, applying overrides, content and site fallbacks.
 *
 * @since 0.1.0
 */
interface DescriptionResolverInterface
{
    /**
     * Resolves the meta description of the page.
     *
     * Resolving is deterministic: equal contexts give equal results.
     *
     * @param PageContext $context
     *   The page being rendered.
     * @return string|null
     *   The description as display text, or null when the page should not emit one. It may already contain HTML
     *   entities but is never trusted as markup, so renderers escape it without double-encoding.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): ?string;
}
