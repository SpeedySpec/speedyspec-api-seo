<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressCanonicalUrlResolver;
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

use function SpeedySpec\WP\Seo\ContractTests\resolverContract;

// The filter only runs for an existing post, so every test here starts with post 7 present.
beforeEach(function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
});

test('canonical passes through get_canonical_url with the post', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    add_filter('get_canonical_url', fn ($url, $post) => $url . '?p=' . $post->ID, 10, 2);
    $r = new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/hello/'));
    expect($r->resolve(post())->toString())->toBe('https://example.com/hello/?p=7');
});

test('canonical is dropped when the filter returns garbage', function (mixed $garbage) {
    add_filter('get_canonical_url', fn () => $garbage);
    expect((new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/hello/')))->resolve(post()))->toBeNull();
})->with([false, '', '/relative/path', null]);

test('canonical filter receives false when the inner resolver has no url', function () {
    $received = 'unset';
    add_filter('get_canonical_url', function ($url) use (&$received) {
        $received = $url;

        return 'https://example.com/from-filter/';
    });
    $r = new WordPressCanonicalUrlResolver(fixedCanonical(null));
    expect($r->resolve(post())->toString())->toBe('https://example.com/from-filter/')
        ->and($received)->toBeFalse();
});

test('canonical skips the filter when the post does not exist', function () {
    unset(FakeWordPress::$posts[7]);
    add_filter('get_canonical_url', fn () => 'https://example.com/filtered/');
    $r = new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/hello/'));
    expect($r->resolve(post())->toString())->toBe('https://example.com/hello/');
});

test('canonical skips the filter for a post that is not published', function (string $status) {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => $status];
    $called = false;
    add_filter('get_canonical_url', function () use (&$called) {
        $called = true;

        return 'https://example.com/filtered/';
    });
    $r = new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/hello/'));
    expect($r->resolve(post())->toString())->toBe('https://example.com/hello/')
        ->and($called)->toBeFalse();
})->with(['draft', 'private']);

test('canonical runs the filter for a published post', function () {
    $called = false;
    add_filter('get_canonical_url', function ($url) use (&$called) {
        $called = true;

        return $url . 'filtered/';
    });
    $r = new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/hello/'));
    expect($r->resolve(post())->toString())->toBe('https://example.com/hello/filtered/')
        ->and($called)->toBeTrue();
});

test('canonical skips the filter for non-singular pages', function (PageContext $context) {
    add_filter('get_canonical_url', fn () => 'https://example.com/filtered/');
    $r = new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/archive/'));
    expect($r->resolve($context)->toString())->toBe('https://example.com/archive/');
})->with([
    'term archive' => [new PageContext(PageKind::TermArchive, 'term', 7)],
    'singular without id' => [new PageContext(PageKind::Singular, 'post')],
]);

test('canonical runs the filter for a static front page', function () {
    add_filter('get_canonical_url', fn ($url, $post) => $url . '?front=' . $post->ID, 10, 2);
    $r = new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/'));
    expect($r->resolve(new PageContext(PageKind::FrontPage, 'post', 7))?->toString())
        ->toBe('https://example.com/?front=7');
});

describe('WordPressCanonicalUrlResolver', function () {
    resolverContract(fn () => new WordPressCanonicalUrlResolver(fixedCanonical('https://example.com/')));
});
