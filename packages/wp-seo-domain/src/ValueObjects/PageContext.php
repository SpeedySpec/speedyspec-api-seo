<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

/**
 * Describes the page being rendered so SEO providers can decide what to emit without reading global state.
 *
 * @since 0.1.0
 */
class PageContext
{
    public readonly int $page;

    /**
     * @param PageKind $kind
     *   The kind of page.
     * @param string|null $objectType
     *   The object family when the page represents one object: 'post', 'term' or 'user'.
     * @param int|null $objectId
     *   The identifier of the represented object.
     * @param int $page
     *   The pagination number; values below one are stored as one.
     * @param string|null $locale
     *   The locale of the page, for example en_US.
     * @param int $status
     *   The HTTP status of the response.
     */
    public function __construct(
        public readonly PageKind $kind,
        public readonly ?string $objectType = null,
        public readonly ?int $objectId = null,
        int $page = 1,
        public readonly ?string $locale = null,
        public readonly int $status = 200,
    ) {
        $this->page = max(1, $page);
    }

    /**
     * Reports whether the page represents a single post-like object.
     *
     * @since 0.1.0
     */
    public function isSingular(): bool
    {
        return $this->kind === PageKind::Singular;
    }

    /**
     * Reports whether the page represents one post, whatever its kind.
     *
     * A static front page is a front page and a post at once, so post-only output such as the canonical link keys
     * off this rather than {@link PageContext::isSingular()}.
     *
     * @since 0.1.0
     */
    public function hasPost(): bool
    {
        return 'post' === $this->objectType && null !== $this->objectId;
    }

    /**
     * Compares two contexts field by field.
     *
     * @since 0.1.0
     */
    public function equals(self $other): bool
    {
        return $this->kind === $other->kind
            && $this->objectType === $other->objectType
            && $this->objectId === $other->objectId
            && $this->page === $other->page
            && $this->locale === $other->locale
            && $this->status === $other->status;
    }
}
