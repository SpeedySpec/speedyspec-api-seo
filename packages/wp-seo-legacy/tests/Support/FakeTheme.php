<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Legacy\Tests\Support;

/**
 * Holds the theme features the stubbed {@link current_theme_supports()} reports, so tests can toggle them.
 *
 * @since 0.1.0
 */
class FakeTheme
{
    public static bool $supportsTitleTag = true;

    /**
     * Restores the default theme, which supports the title tag.
     *
     * @since 0.1.0
     */
    public static function reset(): void
    {
        self::$supportsTitleTag = true;
    }
}
