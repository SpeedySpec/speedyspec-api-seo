<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;

/**
 * Reads site facts from WordPress settings, using display-filtered values so output matches the theme's.
 *
 * @since 0.1.0
 */
class WordPressSiteInfoProvider implements SiteInfoProviderInterface
{
    public function name(): string
    {
        return get_bloginfo('name', 'display');
    }

    public function tagline(): string
    {
        return get_bloginfo('description', 'display');
    }

    public function isPublic(): bool
    {
        return (bool) get_option('blog_public');
    }

    public function usesHttps(): bool
    {
        return is_ssl();
    }
}
