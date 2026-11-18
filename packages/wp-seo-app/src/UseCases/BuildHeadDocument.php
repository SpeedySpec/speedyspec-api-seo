<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\UseCases;

use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorRegistryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadDocumentBuilderInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Builds the head document by running the registered contributors in order.
 *
 * Nothing is cached, so every call resolves at the moment it is made and reflects hooks added since the last one.
 *
 * @since 0.1.0
 */
class BuildHeadDocument implements HeadDocumentBuilderInterface
{
    public function __construct(private readonly HeadContributorRegistryInterface $registry)
    {
    }

    /**
     * Builds a fresh document, leaving out the skipped contributors.
     *
     * Skipped contributors are removed from a copy of the registry, so the registry plugins see is never changed.
     *
     * @since 0.1.0
     */
    public function build(PageContext $context, array $skip = []): HeadDocument
    {
        $registry = $this->registry;
        if ([] !== $skip) {
            $registry = clone $registry;
            foreach ($skip as $id) {
                $registry->remove($id);
            }
        }

        $document = new HeadDocument();
        foreach ($registry->all() as $contributor) {
            $contributor->contribute($document, $context);
        }

        return $document;
    }
}
