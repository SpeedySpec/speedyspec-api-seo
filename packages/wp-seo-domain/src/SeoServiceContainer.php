<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain;

use SpeedySpec\WP\Seo\Domain\Exceptions\ServiceAlreadyResolvedException;
use SpeedySpec\WP\Seo\Domain\Exceptions\ServiceNotRegisteredException;

/**
 * Binds one factory per contract so each SEO interface can be replaced or decorated independently.
 *
 * Bindings are lazy and may only change until a contract is first resolved; after that the cached instance is the
 * single source of truth.
 *
 * @since 0.1.0
 */
class SeoServiceContainer
{
    private static ?self $shared = null;

    /**
     * @var array<string, callable(self): object>
     */
    private array $factories = [];

    /**
     * @var array<string, list<callable(object, self): object>>
     */
    private array $decorators = [];

    /**
     * @var array<string, object>
     */
    private array $instances = [];

    /**
     * Binds a factory to a contract, replacing any unresolved binding and its pending decorators.
     *
     * @param string $contract
     *   The interface or class name the factory satisfies.
     * @param callable(self): object $factory
     *   Builds the implementation; receives the container so it can resolve collaborators.
     *
     * @throws ServiceAlreadyResolvedException
     *   When the contract has already been resolved.
     *
     * @since 0.1.0
     */
    public function add(string $contract, callable $factory): static
    {
        $this->assertNotResolved($contract);
        $this->factories[$contract] = $factory;
        unset($this->decorators[$contract]);

        return $this;
    }

    /**
     * Unbinds a contract together with its pending decorators.
     *
     * @param string $contract
     *   The contract to unbind.
     *
     * @throws ServiceAlreadyResolvedException
     *   When the contract has already been resolved.
     *
     * @since 0.1.0
     */
    public function remove(string $contract): static
    {
        $this->assertNotResolved($contract);
        unset($this->factories[$contract], $this->decorators[$contract]);

        return $this;
    }

    /**
     * Wraps the binding so a plugin can alter one implementation without replacing it.
     *
     * Decorators apply in the order added, so the first added is innermost.
     *
     * @param string $contract
     *   The contract whose binding is decorated.
     * @param callable(object, self): object $decorator
     *   Receives the inner instance and the container, and returns the replacement.
     *
     * @throws ServiceAlreadyResolvedException
     *   When the contract has already been resolved.
     * @throws ServiceNotRegisteredException
     *   When nothing is bound to the contract.
     *
     * @since 0.1.0
     */
    public function extend(string $contract, callable $decorator): static
    {
        $this->assertNotResolved($contract);
        if (!isset($this->factories[$contract])) {
            throw new ServiceNotRegisteredException(sprintf('No service is registered for "%s".', $contract));
        }

        $this->decorators[$contract][] = $decorator;

        return $this;
    }

    /**
     * Checks whether a contract has a binding, so callers can degrade when an implementation is absent.
     *
     * @param string $contract
     *   The contract to look up.
     *
     * @since 0.1.0
     */
    public function has(string $contract): bool
    {
        return isset($this->factories[$contract]);
    }

    /**
     * Resolves a contract, building and decorating it on first use and caching the result.
     *
     * @param string $contract
     *   The contract to resolve.
     *
     * @throws ServiceNotRegisteredException
     *   When nothing is bound to the contract.
     *
     * @since 0.1.0
     */
    public function get(string $contract): object
    {
        if (isset($this->instances[$contract])) {
            return $this->instances[$contract];
        }

        if (!isset($this->factories[$contract])) {
            throw new ServiceNotRegisteredException(sprintf('No service is registered for "%s".', $contract));
        }

        $instance = ($this->factories[$contract])($this);
        foreach ($this->decorators[$contract] ?? [] as $decorator) {
            $instance = $decorator($instance, $this);
        }

        return $this->instances[$contract] = $instance;
    }

    /**
     * Provides the process-wide container that legacy function wrappers resolve services from.
     *
     * @since 0.1.0
     */
    public static function shared(): self
    {
        return self::$shared ??= new self();
    }

    /**
     * Discards the process-wide container so tests do not leak bindings into each other.
     *
     * @since 0.1.0
     */
    public static function resetShared(): void
    {
        self::$shared = null;
    }

    private function assertNotResolved(string $contract): void
    {
        if (isset($this->instances[$contract])) {
            throw new ServiceAlreadyResolvedException(
                sprintf('The service for "%s" was already resolved and can no longer be changed.', $contract)
            );
        }
    }
}
