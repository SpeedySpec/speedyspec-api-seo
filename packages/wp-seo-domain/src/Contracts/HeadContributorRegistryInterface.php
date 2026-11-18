<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

/**
 * Holds the named head contributors so plugins can add, replace or remove one without touching the others.
 *
 * @since 0.1.0
 */
interface HeadContributorRegistryInterface
{
    /**
     * Registers a contributor under an id, replacing any contributor already using that id.
     *
     * @param string $id
     *   The unique name of the contributor.
     * @param HeadContributorInterface $contributor
     *   The contributor to run.
     * @param int $priority
     *   Lower values run earlier; equal priorities keep insertion order.
     *
     * @since 0.1.0
     */
    public function add(string $id, HeadContributorInterface $contributor, int $priority = 10): void;

    /**
     * Unregisters a contributor; removing an unknown id is not an error.
     *
     * @param string $id
     *   The id the contributor was registered under.
     *
     * @since 0.1.0
     */
    public function remove(string $id): void;

    /**
     * Reports whether a contributor is registered under the id.
     *
     * @param string $id
     *   The id to look up.
     *
     * @since 0.1.0
     */
    public function has(string $id): bool;

    /**
     * Lists the contributors in the order they must run.
     *
     * @return list<HeadContributorInterface>
     *   Contributors ordered by priority, then by insertion.
     *
     * @since 0.1.0
     */
    public function all(): array;
}
