<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\UseCases\ResolveTitle;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayContentSource;
use SpeedySpec\WP\Seo\Infra\Memory\ArraySiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\Memory\InMemorySeoOverridesRepository;

test('title joins content title, page label and site name', function () {
    $content = new ArrayContentSource();
    $content->set(post(page: 2), 'Hello', null);
    $r = new ResolveTitle($content, new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(name: 'Site'));
    expect($r->resolve(post(page: 2)))->toBe('Hello - Page 2 - Site');
});

test('front page title is site name then tagline', function () {
    $r = new ResolveTitle(new ArrayContentSource(), new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider('Site', 'Just a site'));
    expect($r->resolve(new PageContext(PageKind::FrontPage)))->toBe('Site - Just a site');
});

test('override title is returned exactly as stored', function () {
    $overrides = new InMemorySeoOverridesRepository();
    $overrides->save('post', 7, new SeoOverrides(title: 'Custom'));
    $content = new ArrayContentSource();
    $content->set(post(), 'Hello', null);
    $r = new ResolveTitle($content, $overrides, new ArraySiteInfoProvider(name: 'Site'));
    expect($r->resolve(post()))->toBe('Custom');
});

test('not found pages get no page label', function () {
    $context = new PageContext(PageKind::NotFound, null, null, 3, null, 404);
    $content = new ArrayContentSource();
    $content->set($context, 'Not found', null);
    $r = new ResolveTitle($content, new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(name: 'Site'));
    expect($r->resolve($context))->toBe('Not found - Site');
});

test('empty parts are skipped and custom separator and label apply', function () {
    $r = new ResolveTitle(new ArrayContentSource(), new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(), '|', 'P%s');
    expect($r->resolve(post()))->toBe('');

    $content = new ArrayContentSource();
    $content->set(post(page: 3), 'Hello', null);
    $r = new ResolveTitle($content, new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(name: 'Site'), '|', 'P%s');
    expect($r->resolve(post(page: 3)))->toBe('Hello | P3 | Site');

    $front = new ResolveTitle(new ArrayContentSource(), new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider('Site', ''));
    expect($front->resolve(new PageContext(PageKind::FrontPage)))->toBe('Site');
});

test('a context without an object id never consults the overrides repository', function () {
    $overrides = new class extends InMemorySeoOverridesRepository
    {
        public int $calls = 0;

        public function find(string $objectType, int $objectId): SeoOverrides
        {
            $this->calls++;

            return parent::find($objectType, $objectId);
        }
    };
    $r = new ResolveTitle(new ArrayContentSource(), $overrides, new ArraySiteInfoProvider(name: 'Site'));
    $r->resolve(new PageContext(PageKind::Search));
    expect($overrides->calls)->toBe(0);
});
