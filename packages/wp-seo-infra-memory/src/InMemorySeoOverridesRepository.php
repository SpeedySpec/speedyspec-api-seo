<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;

/**
 * Keeps SEO overrides in process memory so domain and app code can be tested without a database.
 *
 * @since 0.1.0
 */
class InMemorySeoOverridesRepository implements SeoOverridesRepositoryInterface
{
    /** @var array<string, SeoOverrides> */
    private array $overrides = [];

    public function find(string $objectType, int $objectId): SeoOverrides
    {
        return $this->overrides[$this->key($objectType, $objectId)] ?? SeoOverrides::none();
    }

    public function save(string $objectType, int $objectId, SeoOverrides $overrides): void
    {
        $this->overrides[$this->key($objectType, $objectId)] = $overrides;
    }

    public function delete(string $objectType, int $objectId): void
    {
        unset($this->overrides[$this->key($objectType, $objectId)]);
    }

    private function key(string $objectType, int $objectId): string
    {
        return $objectType . "\0" . $objectId;
    }
}
