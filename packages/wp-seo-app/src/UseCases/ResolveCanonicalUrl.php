<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\UseCases;

use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Chooses the canonical URL so search engines consolidate duplicate pages onto one address.
 *
 * @since 0.1.0
 */
class ResolveCanonicalUrl implements CanonicalUrlResolverInterface
{
    public function __construct(
        private readonly PermalinkSourceInterface $permalinks,
        private readonly SeoOverridesRepositoryInterface $overrides,
        private readonly SiteInfoProviderInterface $site,
    ) {
    }

    /**
     * Resolves the canonical URL, preferring a stored override over the permalink.
     *
     * Error responses have no canonical because they are not meant to be indexed.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): ?Url
    {
        if (200 !== $context->status) {
            return null;
        }

        $url = null;
        if (null !== $context->objectType && null !== $context->objectId) {
            $url = $this->overrides->find($context->objectType, $context->objectId)->canonical;
        }

        $url ??= $this->permalinks->permalinkFor($context);
        if (null === $url) {
            return null;
        }

        return $this->site->usesHttps() ? $url->withScheme('https') : $url;
    }
}
