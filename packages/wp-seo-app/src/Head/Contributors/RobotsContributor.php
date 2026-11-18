<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\Head\Contributors;

use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaAttribute;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Adds the robots meta tag only when there are directives to state.
 *
 * @since 0.1.0
 */
class RobotsContributor implements HeadContributorInterface
{
    public function __construct(private readonly RobotsDirectivesResolverInterface $resolver)
    {
    }

    /**
     * Writes the robots tag into the document.
     *
     * @since 0.1.0
     */
    public function contribute(HeadDocument $document, PageContext $context): void
    {
        $directives = $this->resolver->resolve($context);
        if (!$directives->isEmpty()) {
            $document->setMeta(new MetaTag(MetaAttribute::Name, 'robots', $directives->toString()));
        }
    }
}
