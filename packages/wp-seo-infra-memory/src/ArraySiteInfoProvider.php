<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;

/**
 * Serves fixed site facts so tests can describe any site.
 *
 * @since 0.1.0
 */
class ArraySiteInfoProvider implements SiteInfoProviderInterface
{
    public function __construct(
        private readonly string $name = '',
        private readonly string $tagline = '',
        private readonly bool $isPublic = true,
        private readonly bool $usesHttps = true,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tagline(): string
    {
        return $this->tagline;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function usesHttps(): bool
    {
        return $this->usesHttps;
    }
}
