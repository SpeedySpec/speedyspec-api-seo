<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\LinkTag;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaAttribute;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;

test('setMeta replaces a tag with the same key and keeps order', function () {
    $doc = new HeadDocument();
    $doc->setMeta(new MetaTag(MetaAttribute::Name, 'description', 'one'));
    $doc->setMeta(new MetaTag(MetaAttribute::Property, 'og:title', 'T'));
    $doc->setMeta(new MetaTag(MetaAttribute::Name, 'description', 'two'));
    expect(array_map(fn ($t) => $t->key, $doc->metas()))->toBe(['description', 'og:title'])
        ->and($doc->meta('description')->content)->toBe('two');
});

test('removeMeta and removeLink ignore unknown keys', function () {
    $doc = new HeadDocument();
    $doc->removeMeta('nope');
    $doc->removeLink('nope');
    expect($doc->metas())->toBe([])->and($doc->links())->toBe([])->and($doc->title())->toBeNull();
});

test('setLink replaces a link with the same rel and links keeps insertion order', function () {
    $doc = new HeadDocument();
    $doc->setLink(new LinkTag('canonical', 'https://example.com/a'));
    $doc->setLink(new LinkTag('prev', 'https://example.com/p1'));
    $doc->setLink(new LinkTag('canonical', 'https://example.com/b', ['hreflang' => 'en']));
    expect(array_map(fn ($t) => $t->rel, $doc->links()))->toBe(['canonical', 'prev'])
        ->and($doc->link('canonical')->href)->toBe('https://example.com/b')
        ->and($doc->link('canonical')->attributes)->toBe(['hreflang' => 'en'])
        ->and($doc->link('missing'))->toBeNull();
});

test('removeMeta and removeLink drop existing entries', function () {
    $doc = new HeadDocument();
    $doc->setMeta(new MetaTag(MetaAttribute::Name, 'a', '1'));
    $doc->setMeta(new MetaTag(MetaAttribute::Name, 'b', '2'));
    $doc->setLink(new LinkTag('x', 'https://example.com/'));
    $doc->removeMeta('a');
    $doc->removeLink('x');
    expect(array_map(fn ($t) => $t->key, $doc->metas()))->toBe(['b'])
        ->and($doc->meta('a'))->toBeNull()
        ->and($doc->links())->toBe([]);
});

test('setTitle stores and null clears the title', function () {
    $doc = new HeadDocument();
    $doc->setTitle('Hello');
    expect($doc->title())->toBe('Hello');
    $doc->setTitle(null);
    expect($doc->title())->toBeNull();
});
