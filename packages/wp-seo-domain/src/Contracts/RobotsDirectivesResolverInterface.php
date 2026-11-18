<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;

/**
 * Decides the robots directives of a page so indexing policy lives in one replaceable place.
 *
 * @since 0.1.0
 */
interface RobotsDirectivesResolverInterface
{
    /**
     * Resolves the robots directives of the page.
     *
     * Resolving is deterministic: equal contexts give equal results.
     *
     * @param PageContext $context
     *   The page being rendered.
     * @return RobotsDirectives
     *   The directives; an empty set means the page adds no restriction.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): RobotsDirectives;
}
