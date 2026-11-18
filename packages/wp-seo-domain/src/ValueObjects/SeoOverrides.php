<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

/**
 * Carries per-object editorial values that take precedence over computed SEO output.
 *
 * @since 0.1.0
 */
class SeoOverrides
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?Url $canonical = null,
        public readonly ?RobotsDirectives $robots = null,
        public readonly ?Url $image = null,
    ) {
    }

    /**
     * Creates overrides with no field set.
     *
     * @since 0.1.0
     */
    public static function none(): self
    {
        return new self();
    }

    /**
     * Reports whether no override is set, letting callers skip override handling entirely.
     *
     * @since 0.1.0
     */
    public function isEmpty(): bool
    {
        return $this->title === null
            && $this->description === null
            && $this->canonical === null
            && $this->robots === null
            && $this->image === null;
    }
}
