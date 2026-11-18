<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Tests\Support;

/**
 * Holds the state behind the WordPress function stubs so each test can describe the request it simulates.
 *
 * @since 0.1.0
 */
class FakeWordPress
{
    /** @var array<string, mixed> */
    public static array $options = [];

    /** @var array<string, bool> */
    public static array $conditionals = [];

    /** @var array<string, mixed> */
    public static array $queryVars = [];

    public static int $queriedObjectId = 0;

    public static ?object $queriedObject = null;

    /** @var array<int, object> */
    public static array $posts = [];

    /** @var array<int, string> */
    public static array $permalinks = [];

    /** @var array<string, list<callable>> */
    public static array $filters = [];

    /** @var list<array{string, list<mixed>}> */
    public static array $actions = [];

    /** @var list<array{string, mixed, list<mixed>}> */
    public static array $appliedFilters = [];

    public static string $locale = 'en_US';

    public static bool $ssl = false;

    public static bool $headersSent = false;

    /** @var list<array{string, bool}> */
    public static array $headers = [];

    /**
     * Restores an empty request so state never leaks between tests.
     *
     * @since 0.1.0
     */
    public static function reset(): void
    {
        self::$options = [];
        self::$conditionals = [];
        self::$queryVars = [];
        self::$queriedObjectId = 0;
        self::$queriedObject = null;
        self::$posts = [];
        self::$permalinks = [];
        self::$filters = [];
        self::$actions = [];
        self::$appliedFilters = [];
        self::$locale = 'en_US';
        self::$ssl = false;
        self::$headersSent = false;
        self::$headers = [];
        unset($GLOBALS['page'], $GLOBALS['paged'], $GLOBALS['post']);
        $GLOBALS['wp_query'] = new \stdClass();
    }

    /**
     * Simulates a request before the main query exists, as during an early {@link wp_die()}.
     *
     * @since 0.1.0
     */
    public static function unsetQuery(): void
    {
        unset($GLOBALS['wp_query']);
    }

    /**
     * Fails like WordPress does when a query function dereferences a main query that does not exist yet.
     *
     * @throws \LogicException
     *   When the main query is not set.
     *
     * @since 0.1.0
     */
    public static function requireQuery(string $function): void
    {
        if (!isset($GLOBALS['wp_query'])) {
            throw new \LogicException($function . '() called before the main query exists.');
        }
    }

    /**
     * Counts how many times a filter was applied, so tests can pin when hooks fire.
     *
     * @since 0.1.0
     */
    public static function appliedCount(string $hook): int
    {
        return count(array_filter(self::$appliedFilters, static fn (array $call): bool => $call[0] === $hook));
    }
}
