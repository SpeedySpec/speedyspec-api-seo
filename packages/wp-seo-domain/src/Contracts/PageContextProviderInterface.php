<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Supplies the description of the page being rendered so SEO code never reads framework global state.
 *
 * @since 0.1.0
 */
interface PageContextProviderInterface
{
    /**
     * Describes the page the current request is rendering.
     *
     * Implementations always return a context; when the page cannot be classified they use
     * {@link \SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind::Other}.
     *
     * @since 0.1.0
     */
    public function current(): PageContext;
}
