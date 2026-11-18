<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\Head;

use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorRegistryInterface;

/**
 * Keeps head contributors by id so plugins can add, replace or remove one without touching the others.
 *
 * @since 0.1.0
 */
class HeadContributorRegistry implements HeadContributorRegistryInterface
{
    /** @var array<string, array{contributor: HeadContributorInterface, priority: int, order: int}> */
    private array $entries = [];

    private int $counter = 0;

    /**
     * Registers a contributor, replacing any with the same id and moving it to the end of its priority.
     *
     * @since 0.1.0
     */
    public function add(string $id, HeadContributorInterface $contributor, int $priority = 10): void
    {
        $this->entries[$id] = ['contributor' => $contributor, 'priority' => $priority, 'order' => $this->counter++];
    }

    /**
     * Unregisters a contributor; an unknown id is ignored.
     *
     * @since 0.1.0
     */
    public function remove(string $id): void
    {
        unset($this->entries[$id]);
    }

    /**
     * Reports whether a contributor is registered under the id.
     *
     * @since 0.1.0
     */
    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    /**
     * Lists the contributors ordered by priority and then by insertion.
     *
     * @since 0.1.0
     */
    public function all(): array
    {
        $entries = array_values($this->entries);
        usort(
            $entries,
            static fn (array $a, array $b): int => [$a['priority'], $a['order']] <=> [$b['priority'], $b['order']]
        );

        return array_map(static fn (array $entry): HeadContributorInterface => $entry['contributor'], $entries);
    }
}
