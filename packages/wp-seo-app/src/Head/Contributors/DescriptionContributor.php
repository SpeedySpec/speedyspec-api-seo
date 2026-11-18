<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\Head\Contributors;

use SpeedySpec\WP\Seo\Domain\Contracts\DescriptionResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaAttribute;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Adds the description meta tag only when the page has a description.
 *
 * @since 0.1.0
 */
class DescriptionContributor implements HeadContributorInterface
{
    public function __construct(private readonly DescriptionResolverInterface $resolver)
    {
    }

    /**
     * Writes the description tag into the document.
     *
     * @since 0.1.0
     */
    public function contribute(HeadDocument $document, PageContext $context): void
    {
        $description = $this->resolver->resolve($context);
        if (null !== $description) {
            $document->setMeta(new MetaTag(MetaAttribute::Name, 'description', $description));
        }
    }
}
