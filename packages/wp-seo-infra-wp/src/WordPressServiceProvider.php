<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP;

use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\EventDispatcherInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\ResponseHeaderEmitterInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\NullSeoOverridesRepository;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressContentSource;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressEventDispatcher;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressPageContextProvider;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressPermalinkSource;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressResponseHeaderEmitter;
use SpeedySpec\WP\Seo\Infra\WP\Adapters\WordPressSiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressCanonicalUrlResolver;
use SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressRobotsDirectivesResolver;
use SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressTitleResolver;

/**
 * Binds the WordPress adapters and the filter-preserving resolvers, so a WordPress site keeps today's behaviour.
 *
 * It must be registered after an application-layer provider: the canonical resolver decorates the bound one, and
 * the title and robots resolvers replace theirs.
 *
 * @since 0.1.0
 */
class WordPressServiceProvider
{
    /**
     * Adds the WordPress bindings to the container.
     *
     * The canonical decorator is added first, so a container without an application-layer provider is left
     * untouched when it throws.
     *
     * @throws \SpeedySpec\WP\Seo\Domain\Exceptions\ServiceNotRegisteredException
     *   When no canonical URL resolver is bound yet.
     *
     * @since 0.1.0
     */
    public function register(SeoServiceContainer $container): void
    {
        $container->extend(
            CanonicalUrlResolverInterface::class,
            fn (CanonicalUrlResolverInterface $inner) => new WordPressCanonicalUrlResolver($inner),
        );

        $container->add(PageContextProviderInterface::class, fn () => new WordPressPageContextProvider());
        $container->add(PermalinkSourceInterface::class, fn () => new WordPressPermalinkSource());
        $container->add(SiteInfoProviderInterface::class, fn () => new WordPressSiteInfoProvider());
        $container->add(ContentSourceInterface::class, fn () => new WordPressContentSource());
        $container->add(SeoOverridesRepositoryInterface::class, fn () => new NullSeoOverridesRepository());
        $container->add(ResponseHeaderEmitterInterface::class, fn () => new WordPressResponseHeaderEmitter());
        $container->add(EventDispatcherInterface::class, fn () => new WordPressEventDispatcher());

        $container->add(TitleResolverInterface::class, fn () => new WordPressTitleResolver());
        $container->add(RobotsDirectivesResolverInterface::class, fn () => new WordPressRobotsDirectivesResolver());
    }
}
