<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\Head\Contributors;

use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Sets the document title from the title resolver.
 *
 * @since 0.1.0
 */
class TitleContributor implements HeadContributorInterface
{
    public function __construct(private readonly TitleResolverInterface $resolver)
    {
    }

    /**
     * Writes the title into the document.
     *
     * @since 0.1.0
     */
    public function contribute(HeadDocument $document, PageContext $context): void
    {
        $document->setTitle($this->resolver->resolve($context));
    }
}
