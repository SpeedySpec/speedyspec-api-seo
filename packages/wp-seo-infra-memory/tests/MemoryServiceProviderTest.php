<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\EventDispatcherInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\ResponseHeaderEmitterInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;
use SpeedySpec\WP\Seo\Infra\Memory\ArraySiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\Memory\InMemoryEventDispatcher;
use SpeedySpec\WP\Seo\Infra\Memory\MemoryServiceProvider;

test('binds each data contract separately', function () {
    $c = new SeoServiceContainer();
    (new MemoryServiceProvider())->register($c);
    foreach ([PageContextProviderInterface::class, PermalinkSourceInterface::class, SiteInfoProviderInterface::class,
        ContentSourceInterface::class, SeoOverridesRepositoryInterface::class, ResponseHeaderEmitterInterface::class,
        EventDispatcherInterface::class] as $contract) {
        expect($c->get($contract))->toBeInstanceOf($contract);
    }
});

test('one binding can be replaced without touching the rest', function () {
    $c = new SeoServiceContainer();
    (new MemoryServiceProvider())->register($c);
    $mine = new ArraySiteInfoProvider(name: 'Mine');
    $c->add(SiteInfoProviderInterface::class, fn () => $mine);
    expect($c->get(SiteInfoProviderInterface::class))->toBe($mine)
        ->and($c->get(EventDispatcherInterface::class))->toBeInstanceOf(InMemoryEventDispatcher::class);
});
