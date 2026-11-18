<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Legacy;

use Closure;
use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadDocumentBuilderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\Exceptions\ServiceNotRegisteredException;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Gives the legacy template functions guarded access to the SEO services of the shared container.
 *
 * Template functions run on every page, so a missing binding must degrade to printing nothing rather than to a
 * fatal error. That holds for a partially bound container too: a service whose own collaborators are unbound is
 * treated as unbound. Each part of the head is resolved on its own, when its template function runs, so its hooks
 * fire at the time and in the places they always did.
 *
 * @since 0.1.0
 */
class HeadAccess
{
    /**
     * Ids of the head contributors whose elements the legacy functions print themselves.
     */
    private const array PRINTED_BY_LEGACY_FUNCTIONS = ['title', 'robots', 'canonical'];

    /**
     * Describes the page the current request is rendering.
     *
     * @return PageContext|null
     *   The current page, or null when no page context provider is available.
     *
     * @since 0.1.0
     */
    public static function context(): ?PageContext
    {
        return self::attempt(static function (): ?PageContext {
            $provider = self::service(PageContextProviderInterface::class);

            return $provider instanceof PageContextProviderInterface ? $provider->current() : null;
        });
    }

    /**
     * Resolves the document title of the current page.
     *
     * @return string|null
     *   The title as display text, or null when the title resolver or the page context is unavailable.
     *
     * @since 0.1.0
     */
    public static function title(): ?string
    {
        return self::attempt(static function (): ?string {
            $resolver = self::service(TitleResolverInterface::class);
            $context = $resolver instanceof TitleResolverInterface ? self::context() : null;

            return null === $context ? null : $resolver->resolve($context);
        });
    }

    /**
     * Resolves the robots directives of the current page.
     *
     * @return RobotsDirectives|null
     *   The directives, or null when the robots resolver or the page context is unavailable.
     *
     * @since 0.1.0
     */
    public static function robots(): ?RobotsDirectives
    {
        return self::attempt(static function (): ?RobotsDirectives {
            $resolver = self::service(RobotsDirectivesResolverInterface::class);
            $context = $resolver instanceof RobotsDirectivesResolverInterface ? self::context() : null;

            return null === $context ? null : $resolver->resolve($context);
        });
    }

    /**
     * Resolves the canonical URL of a page, which need not be the current one.
     *
     * @param PageContext $context
     *   The page whose canonical URL is wanted.
     * @return Url|null
     *   The URL, or null when the page has none or the canonical URL resolver is unavailable.
     *
     * @since 0.1.0
     */
    public static function canonical(PageContext $context): ?Url
    {
        return self::attempt(static function () use ($context): ?Url {
            $resolver = self::service(CanonicalUrlResolverInterface::class);

            return $resolver instanceof CanonicalUrlResolverInterface ? $resolver->resolve($context) : null;
        });
    }

    /**
     * Builds the head document of the current page without the elements the legacy functions print themselves.
     *
     * Leaving those contributors out keeps their hooks from firing a second time, or at the wrong time.
     *
     * @return HeadDocument|null
     *   The document, or null when the document builder or the page context is unavailable.
     *
     * @since 0.1.0
     */
    public static function extras(): ?HeadDocument
    {
        return self::attempt(static function (): ?HeadDocument {
            $builder = self::service(HeadDocumentBuilderInterface::class);
            $context = $builder instanceof HeadDocumentBuilderInterface ? self::context() : null;

            return null === $context ? null : $builder->build($context, self::PRINTED_BY_LEGACY_FUNCTIONS);
        });
    }

    /**
     * Resolves a contract from the shared container without failing when it, or one of its collaborators, is unbound.
     *
     * @param class-string $contract
     *   The contract to resolve.
     * @return object|null
     *   The implementation, or null when it cannot be built from the current bindings.
     *
     * @since 0.1.0
     */
    public static function service(string $contract): ?object
    {
        return self::attempt(static function () use ($contract): ?object {
            $container = SeoServiceContainer::shared();

            return $container->has($contract) ? $container->get($contract) : null;
        });
    }

    /**
     * Runs an operation, treating a service that cannot be resolved along the way as unavailable.
     *
     * @template T
     * @param Closure(): (T|null) $operation
     *   The operation to run.
     * @return T|null
     *   The result, or null when the operation needed a service that is not registered.
     */
    private static function attempt(Closure $operation): mixed
    {
        try {
            return $operation();
        } catch (ServiceNotRegisteredException) {
            return null;
        }
    }
}
