<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Supplies the editorial text of a page so title and description resolvers can fall back to it.
 *
 * @since 0.1.0
 */
interface ContentSourceInterface
{
    /**
     * Finds the natural title of the page, such as a post title or term name.
     *
     * @param PageContext $context
     *   The page whose title is wanted.
     * @return string|null
     *   The title, or null when the page has none.
     *
     * @since 0.1.0
     */
    public function titleFor(PageContext $context): ?string;

    /**
     * Finds a short plain-text summary of the page, such as an excerpt or term description.
     *
     * @param PageContext $context
     *   The page whose summary is wanted.
     * @return string|null
     *   The summary, or null when the page has none.
     *
     * @since 0.1.0
     */
    public function summaryFor(PageContext $context): ?string;
}
