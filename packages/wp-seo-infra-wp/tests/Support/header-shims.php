<?php

declare(strict_types=1);

/**
 * Shadows PHP's header functions inside the adapters namespace so the emitter can be tested without sending headers.
 *
 * The emitter calls them unqualified, so under test PHP resolves to these before the global functions.
 */

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

function headers_sent(): bool
{
    return FakeWordPress::$headersSent;
}

function header(string $header, bool $replace = true): void
{
    FakeWordPress::$headers[] = [$header, $replace];
}
