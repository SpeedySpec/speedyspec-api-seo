<?php

declare(strict_types=1);

/**
 * Stubs the WordPress functions the legacy wrappers call that the infra-wp stubs do not define.
 *
 * The infra-wp stubs are loaded alongside these, so no function is defined in both files and the load order cannot
 * decide which stub, and which state, a test gets. Every stub is guarded so the file can never clash with a loaded
 * WordPress.
 */

use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeTheme;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeWordPress;

if (!function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('current_theme_supports')) {
    function current_theme_supports(string $feature, mixed ...$args): bool
    {
        return 'title-tag' === $feature && FakeTheme::$supportsTitleTag;
    }
}

if (!function_exists('is_embed')) {
    function is_embed(): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}
