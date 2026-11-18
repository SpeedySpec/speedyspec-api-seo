<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\AppServiceProvider;
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
use SpeedySpec\WP\Seo\Domain\Exceptions\ServiceNotRegisteredException;
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
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;
use SpeedySpec\WP\Seo\Infra\WP\WordPressServiceProvider;

test('provider wraps the app canonical resolver and rebinds title and robots', function () {
    $c = new SeoServiceContainer();
    (new AppServiceProvider())->register($c);
    (new WordPressServiceProvider())->register($c);
    expect($c->get(CanonicalUrlResolverInterface::class))->toBeInstanceOf(WordPressCanonicalUrlResolver::class)
        ->and($c->get(TitleResolverInterface::class))->toBeInstanceOf(WordPressTitleResolver::class)
        ->and($c->get(RobotsDirectivesResolverInterface::class))->toBeInstanceOf(WordPressRobotsDirectivesResolver::class)
        ->and($c->get(SeoOverridesRepositoryInterface::class))->toBeInstanceOf(NullSeoOverridesRepository::class);
});

test('provider binds each data and output contract to a WordPress adapter', function () {
    $c = new SeoServiceContainer();
    (new AppServiceProvider())->register($c);
    (new WordPressServiceProvider())->register($c);
    expect($c->get(PageContextProviderInterface::class))->toBeInstanceOf(WordPressPageContextProvider::class)
        ->and($c->get(PermalinkSourceInterface::class))->toBeInstanceOf(WordPressPermalinkSource::class)
        ->and($c->get(SiteInfoProviderInterface::class))->toBeInstanceOf(WordPressSiteInfoProvider::class)
        ->and($c->get(ContentSourceInterface::class))->toBeInstanceOf(WordPressContentSource::class)
        ->and($c->get(ResponseHeaderEmitterInterface::class))->toBeInstanceOf(WordPressResponseHeaderEmitter::class)
        ->and($c->get(EventDispatcherInterface::class))->toBeInstanceOf(WordPressEventDispatcher::class);
});

test('wrapped canonical resolver still runs the get_canonical_url filter', function () {
    FakeWordPress::$posts[7] = (object) ['ID' => 7, 'post_status' => 'publish'];
    FakeWordPress::$permalinks[7] = 'https://example.com/hello/';
    add_filter('get_canonical_url', fn ($url) => $url . '#wrapped');
    $c = new SeoServiceContainer();
    (new AppServiceProvider())->register($c);
    (new WordPressServiceProvider())->register($c);
    expect($c->get(CanonicalUrlResolverInterface::class)->resolve(post())?->toString())
        ->toBe('https://example.com/hello/#wrapped');
});

test('provider must be registered after an application-layer provider', function () {
    $c = new SeoServiceContainer();
    expect(fn () => (new WordPressServiceProvider())->register($c))->toThrow(ServiceNotRegisteredException::class)
        ->and($c->has(PageContextProviderInterface::class))->toBeFalse();
});
