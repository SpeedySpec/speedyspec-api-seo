<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Assembles the head document for a page from the registered contributors, so plugins extend the head in one place.
 *
 * Every call builds a fresh document: resolvers fire their hooks at the moment the caller asks, and a page rendered
 * twice never sees a stale result.
 *
 * @since 0.1.0
 */
interface HeadDocumentBuilderInterface
{
    /**
     * Builds the head document of the page by running the registered contributors.
     *
     * @param PageContext $context
     *   The page being rendered.
     * @param list<string> $skip
     *   Ids of contributors not to run, so a caller that prints some elements itself does not resolve them twice;
     *   unknown ids are ignored.
     *
     * @since 0.1.0
     */
    public function build(PageContext $context, array $skip = []): HeadDocument;
}
