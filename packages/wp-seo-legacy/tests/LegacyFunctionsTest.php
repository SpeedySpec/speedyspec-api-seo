<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadRendererInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;
use SpeedySpec\WP\Seo\Legacy\HeadAccess;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeTheme;
use SpeedySpec\WP\Seo\Legacy\Tests\Support\FakeWordPress;

test('prints nothing and does not fail when nothing is bound', function () {
    expect(wp_get_document_title())->toBe('')
        ->and(output('wp_robots') . output('rel_canonical') . output('_wp_render_title_tag') . output('wp_seo_render_head'))->toBe('')
        ->and(wp_get_canonical_url(7))->toBeFalse();
});

test('legacy output formats are byte-identical to WordPress', function () {
    wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello &#8220;x&#8221;', 'A summary.');
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(output('_wp_render_title_tag'))->toBe("<title>Hello &#8220;x&#8221;</title>\n")
        ->and(output('wp_robots'))->toBe("<meta name='robots' content='max-image-preview:large' />\n")
        ->and(output('rel_canonical'))->toBe('<link rel="canonical" href="https://example.com/hello/" />' . "\n")
        ->and(wp_get_canonical_url(7))->toBe('https://example.com/hello/');
});

test('the title tag escapes plain text once and leaves entities alone', function (string $title, string $expected) {
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), $title, null);
    expect(output('_wp_render_title_tag'))->toBe('<title>' . $expected . "</title>\n")
        ->and(wp_get_document_title())->toBe($title);
})->with([
    'already escaped by WordPress' => ['Hello &#8220;x&#8221; &amp; &#039;y&#039;', 'Hello &#8220;x&#8221; &amp; &#039;y&#039;'],
    'plain text' => ['A <b> & "c"', 'A &lt;b&gt; &amp; &quot;c&quot;'],
]);

test('the title tag prints nothing when the head renderer is unbound', function () {
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', null);
    $c->remove(HeadRendererInterface::class);
    expect(output('_wp_render_title_tag'))->toBe('')
        ->and(wp_get_document_title())->toBe('Hello');
});

test('wp_seo_render_head prints only tags no legacy function prints', function () {
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', 'A summary.');
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(output('wp_seo_render_head'))->toBe('<meta name="description" content="A summary." />' . "\n");
});

test('wp_seo_render_head prints nothing when the document has only title, robots and canonical', function () {
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', null);
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    $extras = HeadAccess::extras();
    expect(HeadAccess::title())->toBe('Hello')
        ->and(HeadAccess::robots()?->isEmpty())->toBeFalse()
        ->and(HeadAccess::canonical(post()))->not->toBeNull()
        ->and($extras?->title())->toBeNull()
        ->and($extras?->meta('robots'))->toBeNull()
        ->and($extras?->link('canonical'))->toBeNull()
        ->and(output('wp_seo_render_head'))->toBe('');
});

test('rel_canonical stays silent on pages without a post', function () {
    wpPost();
    $GLOBALS['post'] = FakeWordPress::$posts[7];
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(new PageContext(PageKind::PostsIndex));
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(output('rel_canonical'))->toBe('');
});

test('rel_canonical prints for a static front page', function () {
    wpPost();
    $front = new PageContext(PageKind::FrontPage, 'post', 7);
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set($front);
    $c->get(PermalinkSourceInterface::class)->set($front, Url::fromString('https://example.com/'));
    expect(output('rel_canonical'))->toBe('<link rel="canonical" href="https://example.com/" />' . "\n")
        ->and(wp_get_canonical_url(7))->toBe('https://example.com/');
});

test('title tag respects theme support', function () {
    bindDefaults();
    FakeTheme::$supportsTitleTag = false;
    expect(output('_wp_render_title_tag'))->toBe('');
});

test('legacy sources never name an implementation package', function () {
    $code = file_get_contents(__DIR__ . '/../functions/seo.php') . file_get_contents(__DIR__ . '/../src/HeadAccess.php');
    expect($code)->not->toContain('Seo\\App\\')->not->toContain('Seo\\Infra\\');
});

test('wp_get_document_title is empty when the title resolver is bound but the context provider is not', function () {
    $c = bindDefaults();
    $c->remove(PageContextProviderInterface::class);
    expect($c->has(TitleResolverInterface::class))->toBeTrue()
        ->and(wp_get_document_title())->toBe('');
});

test('wp_get_canonical_url uses the current page when called without a post', function () {
    $GLOBALS['post'] = wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post(7, 2));
    $c->get(PermalinkSourceInterface::class)->set(post(7, 2), Url::fromString('https://example.com/hello/2/'));
    expect(wp_get_canonical_url())->toBe('https://example.com/hello/2/');
});

test('wp_get_canonical_url accepts an object carrying an ID', function () {
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    $post = new stdClass();
    $post->ID = 7;
    expect(wp_get_canonical_url($post))->toBe('https://example.com/hello/');
});

test('wp_get_canonical_url accepts a numeric string id', function () {
    wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(wp_get_canonical_url('7'))->toBe('https://example.com/hello/');
});

test('wp_get_canonical_url is false for a non-numeric string or a zero id', function () {
    wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(wp_get_canonical_url('hello'))->toBeFalse()
        ->and(wp_get_canonical_url(0))->toBeFalse()
        ->and(wp_get_canonical_url('0'))->toBeFalse();
});

test('wp_get_canonical_url does not carry the current page over to another post', function () {
    wpPost(9);
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post(7, 3));
    $c->get(PermalinkSourceInterface::class)->set(post(9), Url::fromString('https://example.com/other/'));
    $c->get(PermalinkSourceInterface::class)->set(post(9, 3), Url::fromString('https://example.com/other/3/'));
    expect(wp_get_canonical_url(9))->toBe('https://example.com/other/');
});

test('wp_get_canonical_url is false without an argument when there is no global post', function () {
    wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(wp_get_canonical_url())->toBeFalse();
});

test('wp_get_canonical_url defaults to the global post on a page without a post', function () {
    $GLOBALS['post'] = wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(new PageContext(PageKind::PostsIndex, page: 2));
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(wp_get_canonical_url())->toBe('https://example.com/hello/');
});

test('wp_get_canonical_url resolves an id and a post object alike', function () {
    $post = wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(new PageContext(PageKind::PostsIndex));
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    expect(wp_get_canonical_url(7))->toBe('https://example.com/hello/')
        ->and(wp_get_canonical_url($post))->toBe('https://example.com/hello/');
});

test('wp_get_canonical_url is false for an unknown id', function () {
    $c = bindDefaults();
    $c->get(PermalinkSourceInterface::class)->set(post(99), Url::fromString('https://example.com/ghost/'));
    expect(wp_get_canonical_url(99))->toBeFalse();
});

test('wp_get_canonical_url is false when the resolver finds no URL', function () {
    wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    expect(wp_get_canonical_url(7))->toBeFalse();
});

test('HeadAccess::service is null for an unbound contract', function () {
    expect(HeadAccess::service(HeadRendererInterface::class))->toBeNull()
        ->and(HeadAccess::context())->toBeNull()
        ->and(HeadAccess::title())->toBeNull()
        ->and(HeadAccess::robots())->toBeNull()
        ->and(HeadAccess::canonical(post()))->toBeNull()
        ->and(HeadAccess::extras())->toBeNull();
});

test('a container missing an infrastructure binding the app layer needs prints nothing and does not fail', function () {
    wpPost();
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', 'A summary.');
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    $c->remove(SeoOverridesRepositoryInterface::class);
    expect(wp_get_document_title())->toBe('')
        ->and(output('_wp_render_title_tag'))->toBe('')
        ->and(output('wp_robots'))->toBe('')
        ->and(output('rel_canonical'))->toBe('')
        ->and(output('wp_seo_render_head'))->toBe('')
        ->and(wp_get_canonical_url(7))->toBeFalse()
        ->and(HeadAccess::service(TitleResolverInterface::class))->toBeNull();
});

test('a missing binding only silences the functions that need it', function () {
    $c = bindDefaults();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->remove(ContentSourceInterface::class);
    expect(wp_get_document_title())->toBe('')
        ->and(output('_wp_render_title_tag'))->toBe('')
        ->and(output('wp_seo_render_head'))->toBe('')
        ->and(output('wp_robots'))->toBe("<meta name='robots' content='max-image-preview:large' />\n");
});

test('HeadAccess::service resolves a bound contract', function () {
    bindDefaults();
    expect(HeadAccess::service(HeadRendererInterface::class))->toBeInstanceOf(HeadRendererInterface::class);
});

test('robots callbacks keep their behaviour on a public site', function () {
    expect(wp_robots_noindex([]))->toBe([])
        ->and(wp_robots_no_robots([]))->toBe(['noindex' => true, 'follow' => true])
        ->and(wp_robots_sensitive_page([]))->toBe(['noindex' => true, 'noarchive' => true])
        ->and(wp_robots_max_image_preview_large([]))->toBe(['max-image-preview' => 'large'])
        ->and(wp_robots_noindex_embeds([]))->toBe([])
        ->and(wp_robots_noindex_search([]))->toBe([]);

    FakeWordPress::$conditionals = ['is_embed' => true, 'is_search' => true];
    expect(wp_robots_noindex_embeds([]))->toBe(['noindex' => true, 'follow' => true])
        ->and(wp_robots_noindex_search([]))->toBe(['noindex' => true, 'follow' => true]);
});

test('robots callbacks keep their behaviour on a non-public site', function () {
    FakeWordPress::$options['blog_public'] = '0';
    expect(wp_robots_noindex([]))->toBe(['noindex' => true, 'nofollow' => true])
        ->and(wp_robots_no_robots([]))->toBe(['noindex' => true, 'nofollow' => true])
        ->and(wp_robots_sensitive_page([]))->toBe(['noindex' => true, 'noarchive' => true])
        ->and(wp_robots_max_image_preview_large([]))->toBe([]);
});
