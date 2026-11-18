<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayContentSource;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayPermalinkSource;
use SpeedySpec\WP\Seo\Infra\Memory\ArraySiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\Memory\RecordingResponseHeaderEmitter;
use SpeedySpec\WP\Seo\Infra\Memory\StaticPageContextProvider;

test('permalink source returns null for an unknown context', function () {
    expect((new ArrayPermalinkSource())->permalinkFor(new PageContext(PageKind::Singular, 'post', 1)))->toBeNull();
});

test('permalink source matches contexts by equality, not identity', function () {
    $source = new ArrayPermalinkSource();
    $source->set(new PageContext(PageKind::Singular, 'post', 1), Url::fromString('https://example.com/a'));

    expect($source->permalinkFor(new PageContext(PageKind::Singular, 'post', 1))?->toString())
        ->toBe('https://example.com/a')
        ->and($source->permalinkFor(new PageContext(PageKind::Singular, 'post', 2)))->toBeNull();
});

test('permalink source replaces the url of an equal context', function () {
    $source = new ArrayPermalinkSource();
    $source->set(new PageContext(PageKind::FrontPage), Url::fromString('https://example.com/a'));
    $source->set(new PageContext(PageKind::FrontPage), Url::fromString('https://example.com/b'));

    expect($source->permalinkFor(new PageContext(PageKind::FrontPage))?->toString())->toBe('https://example.com/b');
});

test('content source returns null for an unknown context and stored values for a known one', function () {
    $source = new ArrayContentSource();
    $context = new PageContext(PageKind::Singular, 'post', 1);
    expect($source->titleFor($context))->toBeNull()
        ->and($source->summaryFor($context))->toBeNull();

    $source->set($context, 'Title', null);

    expect($source->titleFor(new PageContext(PageKind::Singular, 'post', 1)))->toBe('Title')
        ->and($source->summaryFor($context))->toBeNull();
});

test('site info provider defaults and overrides', function () {
    $default = new ArraySiteInfoProvider();
    expect($default->name())->toBe('')
        ->and($default->tagline())->toBe('')
        ->and($default->isPublic())->toBeTrue()
        ->and($default->usesHttps())->toBeTrue();

    $custom = new ArraySiteInfoProvider('N', 'T', false, false);
    expect($custom->name())->toBe('N')
        ->and($custom->tagline())->toBe('T')
        ->and($custom->isPublic())->toBeFalse()
        ->and($custom->usesHttps())->toBeFalse();
});

test('static page context provider defaults to Other and can be changed', function () {
    $provider = new StaticPageContextProvider();
    expect($provider->current()->kind)->toBe(PageKind::Other);

    $context = new PageContext(PageKind::Search);
    $provider->set($context);
    expect($provider->current())->toBe($context);

    expect((new StaticPageContextProvider($context))->current())->toBe($context);
});

test('recording emitter lists emitted headers in order with the replace flag', function () {
    $emitter = new RecordingResponseHeaderEmitter();
    expect($emitter->emitted())->toBe([]);

    $emitter->emit('X-Robots-Tag', 'noindex');
    $emitter->emit('Link', '<https://example.com>', false);

    expect($emitter->emitted())->toBe([
        ['X-Robots-Tag', 'noindex', true],
        ['Link', '<https://example.com>', false],
    ]);
});
