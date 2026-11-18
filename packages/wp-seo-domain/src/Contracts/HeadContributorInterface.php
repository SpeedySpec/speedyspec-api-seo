<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Adds one concern, such as canonical or robots, to the head document so features compose without knowing each other.
 *
 * @since 0.1.0
 */
interface HeadContributorInterface
{
    /**
     * Writes this contributor's elements into the document.
     *
     * @param HeadDocument $document
     *   The document being assembled; mutated in place.
     * @param PageContext $context
     *   The page being rendered.
     *
     * @since 0.1.0
     */
    public function contribute(HeadDocument $document, PageContext $context): void;
}
