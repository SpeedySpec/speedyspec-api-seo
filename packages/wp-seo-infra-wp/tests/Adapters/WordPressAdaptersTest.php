<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\NullSeoOverridesRepository;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressContentSource;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressEventDispatcher;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressResponseHeaderEmitter;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressSiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

use function SpeedySpec\WP\Seo\ContractTests\eventDispatcherContract;

test('site info maps the WordPress options', function () {
    FakeWordPress::$options = ['blogname' => 'Site', 'blogdescription' => 'Tagline', 'blog_public' => '1'];
    FakeWordPress::$ssl = true;
    $info = new WordPressSiteInfoProvider();
    expect($info->name())->toBe('Site')
        ->and($info->tagline())->toBe('Tagline')
        ->and($info->isPublic())->toBeTrue()
        ->and($info->usesHttps())->toBeTrue();
});

test('site info reports a hidden site without https', function () {
    FakeWordPress::$options = ['blog_public' => '0'];
    $info = new WordPressSiteInfoProvider();
    expect($info->isPublic())->toBeFalse()->and($info->usesHttps())->toBeFalse();
});

test('content source titles posts, posts index and terms', function (PageKind $kind, object $queried, ?string $title) {
    FakeWordPress::$queriedObject = $queried;
    expect((new WordPressContentSource())->titleFor(new PageContext($kind)))->toBe($title);
})->with([
    [PageKind::Singular, (object) ['post_title' => 'Hello'], 'Hello'],
    [PageKind::PostsIndex, (object) ['post_title' => 'Blog'], 'Blog'],
    [PageKind::TermArchive, (object) ['name' => 'News'], 'News'],
    [PageKind::AuthorArchive, (object) ['display_name' => 'Ada'], null],
    [PageKind::Search, (object) [], null],
]);

test('content source summarizes with the excerpt, then the content', function (string $excerpt, ?string $summary) {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_excerpt' => $excerpt, 'post_content' => '<p>Body</p>'];
    expect((new WordPressContentSource())->summaryFor(post()))->toBe($summary);
})->with([
    ['Short', 'Short'],
    ['', 'Body'],
]);

test('content source returns a manual excerpt exactly as stored', function () {
    $excerpt = '<b>Kept</b> [gallery] ' . str_repeat('word ', 80);
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_excerpt' => $excerpt, 'post_content' => 'Body'];
    expect((new WordPressContentSource())->summaryFor(post()))->toBe($excerpt);
});

test('content source cleans the content: no style or script text, no shortcodes, no tags', function () {
    FakeWordPress::$posts[7] = (object) [
        'ID' => 7,
        'post_excerpt' => '',
        'post_content' => '<style>.a{color:red}</style><p>Hello [gallery ids="1,2"] <b>world</b></p>'
            . '<script>alert(1)</script> [caption id="x"]Caption text[/caption] end',
    ];
    expect((new WordPressContentSource())->summaryFor(post()))->toBe('Hello world end');
});

test('content source cuts the content to 55 words without a more marker', function () {
    $words = array_map(fn (int $i) => 'w' . $i, range(1, 80));
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_excerpt' => '', 'post_content' => implode(' ', $words)];
    expect((new WordPressContentSource())->summaryFor(post()))->toBe(implode(' ', array_slice($words, 0, 55)));
});

test('content source has no summary for a password-protected post', function (string $excerpt) {
    FakeWordPress::$posts[7] = (object) [
        'ID' => 7,
        'post_excerpt' => $excerpt,
        'post_content' => 'Secret body',
        'post_password' => 'hunter2',
    ];
    expect((new WordPressContentSource())->summaryFor(post()))->toBeNull();
})->with(['', 'Secret excerpt']);

test('content source summarizes a static front page', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_excerpt' => 'Welcome', 'post_content' => 'Body'];
    expect((new WordPressContentSource())->summaryFor(new PageContext(PageKind::FrontPage, 'post', 7)))
        ->toBe('Welcome');
});

test('content source has no summary for missing posts or other pages', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_excerpt' => 'Short', 'post_content' => 'Body'];
    $source = new WordPressContentSource();
    expect($source->summaryFor(post(id: 99)))->toBeNull()
        ->and($source->summaryFor(new PageContext(PageKind::TermArchive, 'term', 7)))->toBeNull();
});

test('content source has no summary for a singular page without an id', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_excerpt' => 'Short', 'post_content' => 'Body'];
    expect((new WordPressContentSource())->summaryFor(new PageContext(PageKind::Singular, 'post')))->toBeNull();
});

test('null overrides repository is always empty', function () {
    $repository = new NullSeoOverridesRepository();
    $repository->save('post', 7, new SeoOverrides(title: 'Custom', canonical: Url::fromString('https://example.com/')));
    expect($repository->find('post', 7)->isEmpty())->toBeTrue();
    $repository->delete('post', 7);
    expect($repository->find('post', 7)->isEmpty())->toBeTrue();
});

test('event dispatcher filters through apply_filters with the extra arguments', function () {
    add_filter('seo.title', fn (string $value, string $suffix) => $value . $suffix);
    expect((new WordPressEventDispatcher())->filter('seo.title', 'Hello', '!'))->toBe('Hello!');
});

test('event dispatcher dispatches through do_action named after the event class', function () {
    $event = new stdClass();
    $received = null;
    add_filter(stdClass::class, function (object $e) use (&$received) {
        $received = $e;
    });
    (new WordPressEventDispatcher())->dispatch($event);
    expect(FakeWordPress::$actions)->toBe([[stdClass::class, [$event]]])
        ->and($received)->toBe($event);
});

describe('WordPressEventDispatcher', function () {
    eventDispatcherContract(fn () => new WordPressEventDispatcher());
});

test('header emitter sends the header', function () {
    (new WordPressResponseHeaderEmitter())->emit('X-Robots-Tag', 'noindex', false);
    expect(FakeWordPress::$headers)->toBe([['X-Robots-Tag: noindex', false]]);
});

test('header emitter does nothing once headers are sent', function () {
    FakeWordPress::$headersSent = true;
    (new WordPressResponseHeaderEmitter())->emit('X-Robots-Tag', 'noindex');
    expect(FakeWordPress::$headers)->toBe([]);
});
