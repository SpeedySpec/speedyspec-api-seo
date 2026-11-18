<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;

/**
 * Persists editorial SEO overrides per object so editors can replace computed values.
 *
 * Objects are identified by type and id together, so a post and a term that share an id never collide.
 *
 * @since 0.1.0
 */
interface SeoOverridesRepositoryInterface
{
    /**
     * Loads the overrides stored for an object.
     *
     * @param string $objectType
     *   The post type, taxonomy or other object type.
     * @param int $objectId
     *   The identifier of the object.
     * @return SeoOverrides
     *   The stored overrides; an empty instance when nothing is stored, so callers never handle null.
     *
     * @since 0.1.0
     */
    public function find(string $objectType, int $objectId): SeoOverrides;

    /**
     * Stores the overrides of an object, replacing whatever was stored before.
     *
     * @param string $objectType
     *   The post type, taxonomy or other object type.
     * @param int $objectId
     *   The identifier of the object.
     * @param SeoOverrides $overrides
     *   The values to store.
     *
     * @since 0.1.0
     */
    public function save(string $objectType, int $objectId, SeoOverrides $overrides): void;

    /**
     * Removes the overrides of an object; removing an object with none is not an error.
     *
     * @param string $objectType
     *   The post type, taxonomy or other object type.
     * @param int $objectId
     *   The identifier of the object.
     *
     * @since 0.1.0
     */
    public function delete(string $objectType, int $objectId): void;
}
