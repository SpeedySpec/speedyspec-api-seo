<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\UseCases;

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;

/**
 * Builds the document title from content, pagination and site identity.
 *
 * @since 0.1.0
 */
class ResolveTitle implements TitleResolverInterface
{
    /**
     * @param string $pageLabel
     *   A sprintf format receiving the page number.
     */
    public function __construct(
        private readonly ContentSourceInterface $content,
        private readonly SeoOverridesRepositoryInterface $overrides,
        private readonly SiteInfoProviderInterface $site,
        private readonly string $separator = '-',
        private readonly string $pageLabel = 'Page %s',
    ) {
    }

    /**
     * Resolves the title, returning an override verbatim so editors control the whole string.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): string
    {
        if (null !== $context->objectType && null !== $context->objectId) {
            $override = $this->overrides->find($context->objectType, $context->objectId)->title;
            if (null !== $override) {
                return $override;
            }
        }

        $label = $context->page >= 2 && PageKind::NotFound !== $context->kind
            ? sprintf($this->pageLabel, $context->page)
            : '';

        $parts = PageKind::FrontPage === $context->kind
            ? [$this->site->name(), $label, $this->site->tagline()]
            : [$this->content->titleFor($context) ?? '', $label, $this->site->name()];

        return implode(" {$this->separator} ", array_filter($parts, static fn (string $part): bool => '' !== $part));
    }
}
