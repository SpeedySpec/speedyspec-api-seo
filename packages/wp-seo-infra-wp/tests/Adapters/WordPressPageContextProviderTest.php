<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressPageContextProvider;
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

test('page context maps conditionals in priority order', function (array $conditionals, PageKind $kind) {
    FakeWordPress::$conditionals = $conditionals;
    expect((new WordPressPageContextProvider())->current()->kind)->toBe($kind);
})->with([
    [['is_404' => true, 'is_singular' => true], PageKind::NotFound],
    [['is_front_page' => true, 'is_home' => true], PageKind::FrontPage],
    [['is_home' => true], PageKind::PostsIndex],
    [['is_singular' => true], PageKind::Singular],
    [['is_tax' => true], PageKind::TermArchive],
    [[], PageKind::Other],
]);

test('page context maps the remaining conditionals', function (array $conditionals, PageKind $kind) {
    FakeWordPress::$conditionals = $conditionals;
    expect((new WordPressPageContextProvider())->current()->kind)->toBe($kind);
})->with([
    [['is_search' => true, 'is_front_page' => true], PageKind::Search],
    [['is_post_type_archive' => true, 'is_tax' => true], PageKind::PostTypeArchive],
    [['is_category' => true], PageKind::TermArchive],
    [['is_tag' => true], PageKind::TermArchive],
    [['is_author' => true, 'is_date' => true], PageKind::AuthorArchive],
    [['is_date' => true], PageKind::DateArchive],
]);

test('page context names the object type of the queried object', function (string $conditional, ?string $type) {
    FakeWordPress::$conditionals = [$conditional => true];
    FakeWordPress::$queriedObjectId = 42;
    $context = (new WordPressPageContextProvider())->current();
    expect($context->objectType)->toBe($type)->and($context->objectId)->toBe(42);
})->with([
    ['is_singular', 'post'],
    ['is_category', 'term'],
    ['is_author', 'user'],
    ['is_date', null],
]);

test('page context has no object id when nothing is queried', function () {
    FakeWordPress::$conditionals = ['is_singular' => true];
    expect((new WordPressPageContextProvider())->current()->objectId)->toBeNull();
});

test('page context takes the larger of the paged and page query vars', function (array $vars, int $page) {
    FakeWordPress::$queryVars = $vars;
    expect((new WordPressPageContextProvider())->current()->page)->toBe($page);
})->with([
    [[], 1],
    [['page' => 3], 3],
    [['paged' => 4, 'page' => 2], 4],
    [['paged' => '5'], 5],
]);

test('page context reports a 404 status for missing pages only', function () {
    FakeWordPress::$conditionals = ['is_404' => true];
    $missing = (new WordPressPageContextProvider())->current();
    FakeWordPress::$conditionals = ['is_singular' => true];
    $found = (new WordPressPageContextProvider())->current();
    expect($missing->status)->toBe(404)->and($found->status)->toBe(200);
});

test('page context carries the site locale', function () {
    FakeWordPress::$locale = 'de_DE';
    expect((new WordPressPageContextProvider())->current()->locale)->toBe('de_DE');
});

test('a static front page is a front page that carries its post', function () {
    FakeWordPress::$conditionals = ['is_front_page' => true, 'is_singular' => true];
    FakeWordPress::$queriedObjectId = 42;
    $context = (new WordPressPageContextProvider())->current();
    expect($context->kind)->toBe(PageKind::FrontPage)
        ->and($context->objectType)->toBe('post')
        ->and($context->objectId)->toBe(42)
        ->and($context->hasPost())->toBeTrue();
});

test('a front page listing posts carries no post', function () {
    FakeWordPress::$conditionals = ['is_front_page' => true, 'is_home' => true];
    $context = (new WordPressPageContextProvider())->current();
    expect($context->objectType)->toBeNull()->and($context->hasPost())->toBeFalse();
});

test('page context is Other without touching the query before the main query exists', function () {
    FakeWordPress::unsetQuery();
    FakeWordPress::$conditionals = ['is_singular' => true];
    FakeWordPress::$queriedObjectId = 42;
    $context = (new WordPressPageContextProvider())->current();
    expect($context->kind)->toBe(PageKind::Other)
        ->and($context->objectType)->toBeNull()
        ->and($context->objectId)->toBeNull()
        ->and($context->page)->toBe(1);
});

test('the query stubs fail like WordPress before the main query exists', function () {
    FakeWordPress::unsetQuery();
    expect(fn () => get_queried_object_id())->toThrow(LogicException::class)
        ->and(fn () => get_query_var('page'))->toThrow(LogicException::class);
});
