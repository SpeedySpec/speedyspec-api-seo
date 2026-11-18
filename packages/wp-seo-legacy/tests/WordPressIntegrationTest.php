<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\AppServiceProvider;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;
use SpeedySpec\WP\Seo\Infra\WP\WordPressServiceProvider;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeTheme;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeWordPress;

// Drives the legacy functions on the WordPress implementation, against the infra-wp WordPress function stubs.

const LEGACY_FILTERS = [
    'pre_get_document_title',
    'document_title_separator',
    'document_title_parts',
    'document_title',
    'wp_robots',
    'get_canonical_url',
];

const TITLE_FILTERS = ['pre_get_document_title', 'document_title_separator', 'document_title_parts', 'document_title'];

beforeEach(function () {
    $container = SeoServiceContainer::shared();
    (new AppServiceProvider())->register($container);
    (new WordPressServiceProvider())->register($container);

    FakeWordPress::$options += ['blogname' => 'Site', 'blogdescription' => 'Tagline'];
    add_filter('wp_robots', 'wp_robots_max_image_preview_large');
});

/**
 * Simulates the main query of a published post, singular unless other conditionals are given.
 */
function queryPost(array $fields = [], array $conditionals = ['is_singular' => true]): object
{
    $post = wpPost(7, $fields + ['post_title' => 'Hello', 'post_excerpt' => '', 'post_content' => 'Body text']);
    FakeWordPress::$conditionals = $conditionals;
    FakeWordPress::$queriedObjectId = 7;
    FakeWordPress::$queriedObject = $post;
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';

    return $post;
}

/**
 * Renders the head as wp_head does: title and robots at priority 1, then the canonical link, then the rest.
 */
function renderHead(): string
{
    return output(function () {
        _wp_render_title_tag();
        wp_robots();
        rel_canonical();
        wp_seo_render_head();
    });
}

/**
 * Counts how often each legacy filter was applied.
 *
 * @return array<string, int>
 */
function legacyFilterCounts(): array
{
    return array_combine(LEGACY_FILTERS, array_map(FakeWordPress::appliedCount(...), LEGACY_FILTERS));
}

test('a singular render prints the legacy head and fires each legacy filter exactly once', function () {
    $post = queryPost();

    expect(renderHead())->toBe(
        "<title>Hello - Site</title>\n"
        . "<meta name='robots' content='max-image-preview:large' />\n"
        . '<link rel="canonical" href="https://example.com/hello/" />' . "\n"
        . '<meta name="description" content="Body text" />' . "\n"
    )->and(legacyFilterCounts())->toBe(array_fill_keys(LEGACY_FILTERS, 1));

    $canonical = array_values(array_filter(
        FakeWordPress::$appliedFilters,
        fn (array $call) => 'get_canonical_url' === $call[0],
    ));
    expect($canonical[0][1])->toBe('https://example.com/hello/')
        ->and($canonical[0][2])->toBe([$post]);
});

test('a theme without title-tag support gets no title tag and no title filters', function () {
    queryPost();
    FakeTheme::$supportsTitleTag = false;

    expect(renderHead())->not->toContain('<title>')
        ->and(legacyFilterCounts())->toBe([
            'pre_get_document_title' => 0,
            'document_title_separator' => 0,
            'document_title_parts' => 0,
            'document_title' => 0,
            'wp_robots' => 1,
            'get_canonical_url' => 1,
        ]);
});

test('wp_robots alone fires only wp_robots and prints the legacy format', function () {
    queryPost();

    expect(output('wp_robots'))->toBe("<meta name='robots' content='max-image-preview:large' />\n")
        ->and(array_column(FakeWordPress::$appliedFilters, 0))->toBe(['wp_robots']);
});

test('wp_robots works before the main query exists', function () {
    FakeWordPress::unsetQuery();

    expect(output('wp_robots'))->toBe("<meta name='robots' content='max-image-preview:large' />\n");
});

test('a static front page prints its canonical link', function () {
    queryPost(conditionals: ['is_front_page' => true, 'is_singular' => true]);
    FakeWordPress::$permalinks[7] = 'https://example.com/';

    expect(output('rel_canonical'))->toBe('<link rel="canonical" href="https://example.com/" />' . "\n");
});

test('a password-protected post prints no description', function () {
    queryPost(['post_password' => 'secret', 'post_excerpt' => 'Secret excerpt', 'post_content' => 'Secret body']);

    expect(output('wp_seo_render_head'))->toBe('')
        ->and(renderHead())->not->toContain('Secret');
});

test('a post without an excerpt prints a trimmed, clean description', function () {
    $words = array_map(fn (int $i) => 'w' . $i, range(1, 70));
    queryPost(['post_content' => '<style>p{color:red}</style><p>[gallery ids="1"]' . implode(' ', $words) . '</p>']);

    expect(output('wp_seo_render_head'))
        ->toBe('<meta name="description" content="' . implode(' ', array_slice($words, 0, 55)) . '" />' . "\n");
});

test('a get_canonical_url filter added after the title tag is honoured by rel_canonical', function () {
    queryPost();
    output('_wp_render_title_tag');
    add_filter('get_canonical_url', fn (string $url) => $url . '?late=1');

    expect(output('rel_canonical'))->toBe('<link rel="canonical" href="https://example.com/hello/?late=1" />' . "\n");
});
