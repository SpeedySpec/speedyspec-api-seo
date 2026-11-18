<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\UseCases\ResolveCanonicalUrl;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayPermalinkSource;
use SpeedySpec\WP\Seo\Infra\Memory\ArraySiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\Memory\InMemorySeoOverridesRepository;

test('canonical forces https when the site uses https', function () {
    $links = new ArrayPermalinkSource();
    $links->set(post(), Url::fromString('http://example.com/hello/'));
    $r = new ResolveCanonicalUrl($links, new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(usesHttps: true));
    expect($r->resolve(post())->toString())->toBe('https://example.com/hello/');
});

test('canonical stays http when the site does not use https', function () {
    $links = new ArrayPermalinkSource();
    $links->set(post(), Url::fromString('http://example.com/hello/'));
    $r = new ResolveCanonicalUrl($links, new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(usesHttps: false));
    expect($r->resolve(post())->toString())->toBe('http://example.com/hello/');
});

test('canonical override wins, non-200 and unknown pages have none', function () {
    $links = new ArrayPermalinkSource();
    $links->set(post(), Url::fromString('https://example.com/hello/'));
    $overrides = new InMemorySeoOverridesRepository();
    $overrides->save('post', 7, new SeoOverrides(canonical: Url::fromString('https://example.com/original/')));
    $r = new ResolveCanonicalUrl($links, $overrides, new ArraySiteInfoProvider());
    expect($r->resolve(post())->toString())->toBe('https://example.com/original/')
        ->and($r->resolve(post(status: 404)))->toBeNull()
        ->and($r->resolve(post(id: 99)))->toBeNull();
});
