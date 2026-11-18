<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App;

use SpeedySpec\WP\Seo\App\Head\Contributors\CanonicalContributor;
use SpeedySpec\WP\Seo\App\Head\Contributors\DescriptionContributor;
use SpeedySpec\WP\Seo\App\Head\Contributors\RobotsContributor;
use SpeedySpec\WP\Seo\App\Head\Contributors\TitleContributor;
use SpeedySpec\WP\Seo\App\Head\HeadContributorRegistry;
use SpeedySpec\WP\Seo\App\Head\HtmlHeadRenderer;
use SpeedySpec\WP\Seo\App\UseCases\BuildHeadDocument;
use SpeedySpec\WP\Seo\App\UseCases\ResolveCanonicalUrl;
use SpeedySpec\WP\Seo\App\UseCases\ResolveDescription;
use SpeedySpec\WP\Seo\App\UseCases\ResolveRobotsDirectives;
use SpeedySpec\WP\Seo\App\UseCases\ResolveTitle;
use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\DescriptionResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorRegistryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadDocumentBuilderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadRendererInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SiteInfoProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;

/**
 * Binds every application-layer contract individually so a plugin can replace one without touching the others.
 *
 * Collaborators are fetched inside each factory, so a binding replaced after registration is still honoured.
 *
 * @since 0.1.0
 */
class AppServiceProvider
{
    /**
     * Adds the application-layer bindings to the container.
     *
     * @since 0.1.0
     */
    public function register(SeoServiceContainer $container): void
    {
        $container->add(CanonicalUrlResolverInterface::class, fn (SeoServiceContainer $c) => new ResolveCanonicalUrl(
            $c->get(PermalinkSourceInterface::class),
            $c->get(SeoOverridesRepositoryInterface::class),
            $c->get(SiteInfoProviderInterface::class),
        ));
        $container->add(TitleResolverInterface::class, fn (SeoServiceContainer $c) => new ResolveTitle(
            $c->get(ContentSourceInterface::class),
            $c->get(SeoOverridesRepositoryInterface::class),
            $c->get(SiteInfoProviderInterface::class),
        ));
        $container->add(DescriptionResolverInterface::class, fn (SeoServiceContainer $c) => new ResolveDescription(
            $c->get(ContentSourceInterface::class),
            $c->get(SeoOverridesRepositoryInterface::class),
        ));
        $container->add(
            RobotsDirectivesResolverInterface::class,
            fn (SeoServiceContainer $c) => new ResolveRobotsDirectives(
                $c->get(SeoOverridesRepositoryInterface::class),
                $c->get(SiteInfoProviderInterface::class),
            ),
        );
        $container->add(HeadContributorRegistryInterface::class, function (SeoServiceContainer $c) {
            $registry = new HeadContributorRegistry();
            $registry->add('title', new TitleContributor($c->get(TitleResolverInterface::class)), 10);
            $registry->add('description', new DescriptionContributor($c->get(DescriptionResolverInterface::class)), 20);
            $registry->add('robots', new RobotsContributor($c->get(RobotsDirectivesResolverInterface::class)), 30);
            $registry->add('canonical', new CanonicalContributor($c->get(CanonicalUrlResolverInterface::class)), 40);

            return $registry;
        });
        $container->add(HeadDocumentBuilderInterface::class, fn (SeoServiceContainer $c) => new BuildHeadDocument(
            $c->get(HeadContributorRegistryInterface::class),
        ));
        $container->add(HeadRendererInterface::class, fn () => new HtmlHeadRenderer());
    }
}
