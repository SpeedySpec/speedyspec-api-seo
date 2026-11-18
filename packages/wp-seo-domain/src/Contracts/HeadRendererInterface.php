<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\LinkTag;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;

/**
 * Turns head elements into markup so escaping rules live in one place and output formats can be swapped.
 *
 * Every value is display text that may already contain HTML entities but is never trusted as markup: renderers
 * escape it exactly once, without double-encoding existing entities.
 *
 * @since 0.1.0
 */
interface HeadRendererInterface
{
    /**
     * Renders the title element.
     *
     * @param string $title
     *   The title as display text, escaped here without double-encoding existing entities.
     *
     * @since 0.1.0
     */
    public function renderTitle(string $title): string;

    /**
     * Renders one meta element.
     *
     * @param MetaTag $tag
     *   The tag to render.
     *
     * @since 0.1.0
     */
    public function renderMeta(MetaTag $tag): string;

    /**
     * Renders one link element.
     *
     * @param LinkTag $tag
     *   The tag to render.
     *
     * @since 0.1.0
     */
    public function renderLink(LinkTag $tag): string;

    /**
     * Renders every element of the document.
     *
     * @param HeadDocument $document
     *   The document to render; an empty document renders as an empty string.
     *
     * @since 0.1.0
     */
    public function render(HeadDocument $document): string;
}
