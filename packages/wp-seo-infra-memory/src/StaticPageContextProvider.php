<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;

/**
 * Provides a page context chosen by the caller so tests can simulate any page.
 *
 * @since 0.1.0
 */
class StaticPageContextProvider implements PageContextProviderInterface
{
    private PageContext $context;

    /**
     * @param PageContext|null $context
     *   The initial page; null means a page of kind {@link PageKind::Other}.
     */
    public function __construct(?PageContext $context = null)
    {
        $this->context = $context ?? new PageContext(PageKind::Other);
    }

    /**
     * Changes the page returned from now on.
     *
     * @since 0.1.0
     */
    public function set(PageContext $context): void
    {
        $this->context = $context;
    }

    public function current(): PageContext
    {
        return $this->context;
    }
}
