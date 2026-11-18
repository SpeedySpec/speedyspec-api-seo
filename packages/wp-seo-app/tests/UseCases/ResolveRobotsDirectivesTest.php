<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\UseCases\ResolveRobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Infra\Memory\ArraySiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\Memory\InMemorySeoOverridesRepository;

test('robots defaults by site visibility and page kind', function () {
    $public = new ResolveRobotsDirectives(new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(isPublic: true));
    $private = new ResolveRobotsDirectives(new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider(isPublic: false));
    expect($public->resolve(post())->toString())->toBe('max-image-preview:large')
        ->and($public->resolve(new PageContext(PageKind::Search))->toString())->toBe('noindex, follow')
        ->and($public->resolve(new PageContext(PageKind::NotFound))->toString())->toBe('noindex, follow')
        ->and($private->resolve(post())->toString())->toBe('noindex, nofollow');
});

test('robots override merges last', function () {
    $overrides = new InMemorySeoOverridesRepository();
    $overrides->save('post', 7, new SeoOverrides(robots: RobotsDirectives::none()->with('noarchive')));
    $r = new ResolveRobotsDirectives($overrides, new ArraySiteInfoProvider());
    expect($r->resolve(post())->toString())->toBe('max-image-preview:large, noarchive');
});
