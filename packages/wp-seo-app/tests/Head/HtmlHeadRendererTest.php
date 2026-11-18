<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\Head\HtmlHeadRenderer;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\LinkTag;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaAttribute;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;

test('renderer escapes once and does not double-encode entities', function () {
    $r = new HtmlHeadRenderer();
    expect($r->renderMeta(new MetaTag(MetaAttribute::Name, 'description', 'Tom &amp; "Jerry" &#8220;x&#8221; <b>')))
        ->toBe('<meta name="description" content="Tom &amp; &quot;Jerry&quot; &#8220;x&#8221; &lt;b&gt;" />' . "\n");
});

test('renderer uses the property attribute for property tags', function () {
    expect((new HtmlHeadRenderer())->renderMeta(new MetaTag(MetaAttribute::Property, 'og:title', 'T')))
        ->toBe('<meta property="og:title" content="T" />' . "\n");
});

test('renderer keeps entities in a title already escaped by WordPress', function () {
    expect((new HtmlHeadRenderer())->renderTitle('Hello &#8220;x&#8221; &amp; &#039;y&#039;'))
        ->toBe('<title>Hello &#8220;x&#8221; &amp; &#039;y&#039;</title>' . "\n");
});

test('renderer escapes the title', function () {
    expect((new HtmlHeadRenderer())->renderTitle('A <b> & "c"'))
        ->toBe('<title>A &lt;b&gt; &amp; &quot;c&quot;</title>' . "\n");
});

test('renderer renders a link with extra attributes after href, escaping values', function () {
    $tag = new LinkTag('alternate', 'https://example.com/?a=1&b=2', ['hreflang' => 'en', 'title' => 'a "b"']);

    expect((new HtmlHeadRenderer())->renderLink($tag))
        ->toBe('<link rel="alternate" href="https://example.com/?a=1&amp;b=2" hreflang="en" title="a &quot;b&quot;" />' . "\n");
});

test('renderer skips extra link attributes whose name is not a valid attribute name', function () {
    $tag = new LinkTag('alternate', 'https://example.com/', [
        'ti"tle' => 'x',
        '1st' => 'x',
        'on load' => 'x',
        'data-x' => 'y',
        'xml:lang' => 'en',
        "a\n" => 'x',
    ]);

    expect((new HtmlHeadRenderer())->renderLink($tag))
        ->toBe('<link rel="alternate" href="https://example.com/" data-x="y" xml:lang="en" />' . "\n");
});

test('renderer renders title, then metas, then links', function () {
    $doc = new HeadDocument();
    $doc->setLink(new LinkTag('canonical', 'https://example.com/'));
    $doc->setMeta(new MetaTag(MetaAttribute::Name, 'description', 'D'));
    $doc->setTitle('T');

    expect((new HtmlHeadRenderer())->render($doc))
        ->toBe(
            '<title>T</title>' . "\n"
            . '<meta name="description" content="D" />' . "\n"
            . '<link rel="canonical" href="https://example.com/" />' . "\n"
        );
});

test('renderer renders an empty document as an empty string', function () {
    expect((new HtmlHeadRenderer())->render(new HeadDocument()))->toBe('');
});
