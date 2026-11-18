<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\EventDispatcherInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\ResponseHeaderEmitterInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;

/**
 * Binds the in-memory adapters to the data and output contracts, one binding each so any can be replaced alone.
 *
 * @since 0.1.0
 */
class MemoryServiceProvider
{
    /**
     * Adds the in-memory bindings to the container.
     *
     * @since 0.1.0
     */
    public function register(SeoServiceContainer $container): void
    {
        $container->add(PageContextProviderInterface::class, fn () => new StaticPageContextProvider());
        $container->add(PermalinkSourceInterface::class, fn () => new ArrayPermalinkSource());
        $container->add(SiteInfoProviderInterface::class, fn () => new ArraySiteInfoProvider());
        $container->add(ContentSourceInterface::class, fn () => new ArrayContentSource());
        $container->add(SeoOverridesRepositoryInterface::class, fn () => new InMemorySeoOverridesRepository());
        $container->add(ResponseHeaderEmitterInterface::class, fn () => new RecordingResponseHeaderEmitter());
        $container->add(EventDispatcherInterface::class, fn () => new InMemoryEventDispatcher());
    }
}
