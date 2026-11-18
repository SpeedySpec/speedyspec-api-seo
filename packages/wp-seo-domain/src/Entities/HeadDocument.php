<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Entities;

use SpeedySpec\WP\Seo\Domain\ValueObjects\LinkTag;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;

/**
 * Collects the SEO-relevant head elements so providers and modifiers share one editable model before rendering.
 *
 * Meta tags are unique by key and link tags are unique by rel; setting an existing one replaces it in place.
 *
 * @since 0.1.0
 */
class HeadDocument
{
    private ?string $title = null;

    /** @var array<string, MetaTag> */
    private array $metas = [];

    /** @var array<string, LinkTag> */
    private array $links = [];

    /**
     * Retrieves the document title.
     *
     * @since 0.1.0
     */
    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the document title; null clears it.
     *
     * @since 0.1.0
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Retrieves the meta tag with the given key.
     *
     * @since 0.1.0
     */
    public function meta(string $key): ?MetaTag
    {
        return $this->metas[$key] ?? null;
    }

    /**
     * Adds a meta tag, replacing any tag with the same key.
     *
     * @since 0.1.0
     */
    public function setMeta(MetaTag $tag): void
    {
        $this->metas[$tag->key] = $tag;
    }

    /**
     * Removes the meta tag with the given key, if present.
     *
     * @since 0.1.0
     */
    public function removeMeta(string $key): void
    {
        unset($this->metas[$key]);
    }

    /**
     * Lists all meta tags in insertion order.
     *
     * @return list<MetaTag>
     *   The meta tags.
     *
     * @since 0.1.0
     */
    public function metas(): array
    {
        return array_values($this->metas);
    }

    /**
     * Retrieves the link tag with the given rel.
     *
     * @since 0.1.0
     */
    public function link(string $rel): ?LinkTag
    {
        return $this->links[$rel] ?? null;
    }

    /**
     * Adds a link tag, replacing any tag with the same rel.
     *
     * @since 0.1.0
     */
    public function setLink(LinkTag $tag): void
    {
        $this->links[$tag->rel] = $tag;
    }

    /**
     * Removes the link tag with the given rel, if present.
     *
     * @since 0.1.0
     */
    public function removeLink(string $rel): void
    {
        unset($this->links[$rel]);
    }

    /**
     * Lists all link tags in insertion order.
     *
     * @return list<LinkTag>
     *   The link tags.
     *
     * @since 0.1.0
     */
    public function links(): array
    {
        return array_values($this->links);
    }
}
