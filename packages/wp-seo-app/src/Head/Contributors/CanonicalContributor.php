<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\Head\Contributors;

use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\LinkTag;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Adds the canonical link only when the page has a canonical URL.
 *
 * @since 0.1.0
 */
class CanonicalContributor implements HeadContributorInterface
{
    public function __construct(private readonly CanonicalUrlResolverInterface $resolver)
    {
    }

    /**
     * Writes the canonical link into the document.
     *
     * @since 0.1.0
     */
    public function contribute(HeadDocument $document, PageContext $context): void
    {
        $url = $this->resolver->resolve($context);
        if (null !== $url) {
            $document->setLink(new LinkTag('canonical', $url->toString()));
        }
    }
}
