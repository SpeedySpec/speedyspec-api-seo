<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Legacy\Tests\Support;

use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress as InfraFakeWordPress;

/**
 * Holds the state behind the WordPress function stubs, shared with the infra-wp stubs so both sets read one request.
 *
 * Static properties are inherited, not redeclared, so this class and the infra-wp one see the same values.
 *
 * @since 0.1.0
 */
class FakeWordPress extends InfraFakeWordPress
{
    /**
     * Restores a public site serving an ordinary request.
     *
     * @since 0.1.0
     */
    public static function reset(): void
    {
        parent::reset();
        self::$options = ['blog_public' => '1'];
    }
}
