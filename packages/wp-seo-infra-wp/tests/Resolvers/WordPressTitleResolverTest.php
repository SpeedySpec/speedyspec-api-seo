<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressTitleResolver;
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

use function SpeedySpec\WP\Seo\ContractTests\resolverContract;

test('title follows the WordPress algorithm and its filters', function () {
    FakeWordPress::$conditionals = ['is_search' => true];
    FakeWordPress::$queryVars = ['s' => 'cats'];
    FakeWordPress::$options['blogname'] = 'Site';
    add_filter('document_title_separator', fn () => '|');
    expect((new WordPressTitleResolver())->resolve(new PageContext(PageKind::Search)))
        ->toBe('Search Results for &#8220;cats&#8221; | Site');
});

test('pre_get_document_title short-circuits', function () {
    add_filter('pre_get_document_title', fn () => 'Forced');
    expect((new WordPressTitleResolver())->resolve(post()))->toBe('Forced');
});

test('title uses the tagline on the front page', function () {
    FakeWordPress::$conditionals = ['is_front_page' => true, 'is_home' => true];
    FakeWordPress::$options = ['blogname' => 'Site', 'blogdescription' => 'Just another site'];
    expect((new WordPressTitleResolver())->resolve(new PageContext(PageKind::FrontPage)))
        ->toBe('Site - Just another site');
});

test('title adds the page number from the page and paged globals', function () {
    FakeWordPress::$conditionals = ['is_singular' => true];
    FakeWordPress::$queriedObject = (object) ['post_title' => 'Hello'];
    FakeWordPress::$options['blogname'] = 'Site';
    $GLOBALS['page'] = 3;
    $GLOBALS['paged'] = 0;
    expect((new WordPressTitleResolver())->resolve(post(page: 3)))->toBe('Hello - Page 3 - Site');
});

test('title omits the page number on a 404', function () {
    FakeWordPress::$conditionals = ['is_404' => true];
    FakeWordPress::$options['blogname'] = 'Site';
    $GLOBALS['paged'] = 2;
    expect((new WordPressTitleResolver())->resolve(new PageContext(PageKind::NotFound)))
        ->toBe('Page not found - Site');
});

test('title picks the source for each kind of page', function (array $conditionals, ?object $queried, string $title) {
    FakeWordPress::$conditionals = $conditionals;
    FakeWordPress::$queriedObject = $queried;
    expect((new WordPressTitleResolver())->resolve(new PageContext(PageKind::Other)))->toBe($title);
})->with([
    'post type archive' => [
        ['is_post_type_archive' => true],
        (object) ['labels' => (object) ['name' => 'Books']],
        'Books',
    ],
    'taxonomy' => [['is_tax' => true], (object) ['name' => 'Genre'], 'Genre'],
    'posts index' => [['is_home' => true], (object) ['post_title' => 'Blog'], 'Blog'],
    'category' => [['is_category' => true], (object) ['name' => 'News'], 'News'],
    'author' => [['is_author' => true], (object) ['display_name' => 'Ada'], 'Ada'],
    'author without object' => [['is_author' => true], null, ''],
    'year' => [['is_year' => true], null, 'date(Y)'],
    'month' => [['is_month' => true], null, 'date(F Y)'],
    'day' => [['is_day' => true], null, 'date()'],
]);

test('title filters run in WordPress order', function () {
    FakeWordPress::$conditionals = ['is_singular' => true];
    FakeWordPress::$queriedObject = (object) ['post_title' => 'Hello'];
    FakeWordPress::$options['blogname'] = 'Site';
    $calls = [];
    add_filter('pre_get_document_title', function ($t) use (&$calls) {
        $calls[] = 'pre';

        return $t;
    });
    add_filter('document_title_separator', function ($s) use (&$calls) {
        $calls[] = 'separator';

        return $s;
    });
    add_filter('document_title_parts', function (array $parts) use (&$calls) {
        $calls[] = 'parts';
        unset($parts['site']);

        return $parts;
    });
    add_filter('document_title', function ($t) use (&$calls) {
        $calls[] = 'title';

        return $t . '!';
    });
    expect((new WordPressTitleResolver())->resolve(post()))->toBe('Hello!')
        ->and($calls)->toBe(['pre', 'separator', 'parts', 'title']);
});

describe('WordPressTitleResolver', function () {
    resolverContract(fn () => new WordPressTitleResolver());
});
