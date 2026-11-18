<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\AppServiceProvider;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Infra\Memory\MemoryServiceProvider;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeTheme;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeWordPress;

// The infra-wp test stubs are reused by relative path; they define every WordPress function both packages need
// except the few in this package's own stub file, so no function has two competing definitions.
require_once __DIR__ . '/../../wp-seo-infra-wp/tests/Support/FakeWordPress.php';
require_once __DIR__ . '/../../wp-seo-infra-wp/tests/Support/wordpress-functions.php';
require_once __DIR__ . '/Support/wordpress-functions.php';

pest()->beforeEach(function () {
    SeoServiceContainer::resetShared();
    FakeTheme::reset();
    FakeWordPress::reset();
})->in(__DIR__);

function post(int $id = 7, int $page = 1, int $status = 200): PageContext
{
    return new PageContext(PageKind::Singular, 'post', $id, $page, null, $status);
}

/**
 * Makes a published post visible to the stubbed {@link get_post()}, as {@link wp_get_canonical_url()} looks it up.
 */
function wpPost(int $id = 7, array $fields = []): object
{
    return FakeWordPress::$posts[$id] = (object) ($fields + ['ID' => $id, 'post_status' => 'publish']);
}

function bindDefaults(): SeoServiceContainer
{
    $container = SeoServiceContainer::shared();
    (new MemoryServiceProvider())->register($container);
    (new AppServiceProvider())->register($container);

    return $container;
}

function output(callable $fn): string
{
    ob_start();
    try {
        $fn();
    } finally {
        $output = (string) ob_get_clean();
    }

    return $output;
}
