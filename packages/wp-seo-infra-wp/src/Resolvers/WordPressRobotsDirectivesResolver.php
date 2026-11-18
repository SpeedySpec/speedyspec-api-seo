<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Resolvers;

use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;

/**
 * Collects robots directives from the `wp_robots` filter as {@link wp_robots()} does, so core and plugin callbacks
 * that already shape the robots meta tag keep working.
 *
 * The page context is ignored because those callbacks read the global query themselves. A filter that returns
 * something other than an array, or invalid directives, yields no directives for those entries rather than an error.
 *
 * Because this replaces the application robots resolver, {@link SeoOverridesRepositoryInterface} is not consulted
 * for robots directives under WordPress in phase 1; this is revisited when WordPress-backed overrides arrive.
 *
 * @since 0.1.0
 */
class WordPressRobotsDirectivesResolver implements RobotsDirectivesResolverInterface
{
    public function resolve(PageContext $context): RobotsDirectives
    {
        return RobotsDirectives::fromArray(apply_filters('wp_robots', []));
    }
}
