<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressPermalinkSource;
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

test('permalink source returns nothing for unpublished or missing posts', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'draft'];
    $s = new WordPressPermalinkSource();
    expect($s->permalinkFor(post()))->toBeNull()->and($s->permalinkFor(post(id: 99)))->toBeNull();
});

test('permalink source returns the permalink of a published post', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    expect((new WordPressPermalinkSource())->permalinkFor(post())->toString())->toBe('https://example.com/hello/');
});

test('permalink source does not apply the get_canonical_url filter', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    add_filter('get_canonical_url', fn () => 'https://example.com/filtered/');
    expect((new WordPressPermalinkSource())->permalinkFor(post())->toString())->toBe('https://example.com/hello/');
});

test('permalink source paginates the queried post', function (string $structure, string $expected) {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = $structure === '' ? 'https://example.com/?p=7' : 'https://example.com/hello/';
    FakeWordPress::$options['permalink_structure'] = $structure;
    FakeWordPress::$queriedObjectId = 7;
    FakeWordPress::$queryVars['page'] = 2;
    expect((new WordPressPermalinkSource())->permalinkFor(post(page: 2))->toString())->toBe($expected);
})->with([
    'plain permalinks' => ['', 'https://example.com/?p=7&page=2'],
    'pretty permalinks' => ['/%postname%/', 'https://example.com/hello/2/'],
]);

test('permalink source ignores pagination for a post that is not queried', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    FakeWordPress::$queriedObjectId = 8;
    FakeWordPress::$queryVars['page'] = 2;
    expect((new WordPressPermalinkSource())->permalinkFor(post())->toString())->toBe('https://example.com/hello/');
});

test('permalink source uses the comments page link for a comment page', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    FakeWordPress::$queriedObjectId = 7;
    FakeWordPress::$queryVars['cpage'] = 3;
    expect((new WordPressPermalinkSource())->permalinkFor(post())->toString())
        ->toBe('https://example.com/hello/comment-page-3/#comments');
});

test('permalink source returns nothing for pages without a post', function (PageContext $context) {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    expect((new WordPressPermalinkSource())->permalinkFor($context))->toBeNull();
})->with([
    'term archive' => [new PageContext(PageKind::TermArchive, 'term', 7)],
    'author archive' => [new PageContext(PageKind::AuthorArchive, 'user', 7)],
    'front page listing posts' => [new PageContext(PageKind::FrontPage)],
    'posts index' => [new PageContext(PageKind::PostsIndex, null, 7)],
]);

test('permalink source returns the permalink of a static front page', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/';
    expect((new WordPressPermalinkSource())->permalinkFor(new PageContext(PageKind::FrontPage, 'post', 7))?->toString())
        ->toBe('https://example.com/');
});

test('permalink source returns nothing for a singular page without an id', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    expect((new WordPressPermalinkSource())->permalinkFor(new PageContext(PageKind::Singular, 'post')))->toBeNull();
});
