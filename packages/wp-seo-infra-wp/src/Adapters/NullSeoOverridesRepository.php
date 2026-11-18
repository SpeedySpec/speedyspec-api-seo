<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;

/**
 * Reports that no object has SEO overrides, so the WordPress binding is complete before stored overrides exist.
 *
 * WordPress-backed storage is deferred to a later phase, and infrastructure packages may not require each other, so
 * the in-memory repository cannot stand in. Writes are discarded by design in phase 1, which is why this class is
 * exempt from the `seoOverridesRepositoryContract` round-trip tests.
 *
 * @since 0.1.0
 */
class NullSeoOverridesRepository implements SeoOverridesRepositoryInterface
{
    public function find(string $objectType, int $objectId): SeoOverrides
    {
        return SeoOverrides::none();
    }

    public function save(string $objectType, int $objectId, SeoOverrides $overrides): void
    {
    }

    public function delete(string $objectType, int $objectId): void
    {
    }
}
