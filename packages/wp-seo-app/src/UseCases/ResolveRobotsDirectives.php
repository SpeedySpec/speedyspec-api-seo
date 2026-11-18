<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\UseCases;

use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;

/**
 * Decides robots directives from site visibility, page kind and per-object overrides.
 *
 * @since 0.1.0
 */
class ResolveRobotsDirectives implements RobotsDirectivesResolverInterface
{
    public function __construct(
        private readonly SeoOverridesRepositoryInterface $overrides,
        private readonly SiteInfoProviderInterface $site,
    ) {
    }

    /**
     * Resolves directives, letting an override add to or replace individual defaults.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): RobotsDirectives
    {
        $directives = match (true) {
            !$this->site->isPublic() => RobotsDirectives::none()->with('noindex')->with('nofollow'),
            PageKind::Search === $context->kind,
            PageKind::NotFound === $context->kind => RobotsDirectives::none()->with('noindex')->with('follow'),
            default => RobotsDirectives::none()->with('max-image-preview', 'large'),
        };

        if (null !== $context->objectType && null !== $context->objectId) {
            $override = $this->overrides->find($context->objectType, $context->objectId)->robots;
            if (null !== $override) {
                $directives = $directives->merge($override);
            }
        }

        return $directives;
    }
}
