<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Resolvers;

use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Passes a post's canonical URL through the `get_canonical_url` filter, the last step of
 * {@link wp_get_canonical_url()}, so plugins hooked there still have the final word.
 *
 * It decorates the application resolver rather than replacing it, keeping overrides and the https upgrade. As in
 * WordPress, the filter runs only for an existing, published post, so callbacks never see drafts or private posts;
 * otherwise the inner result is returned unfiltered. The filter receives false when there is no URL, as WordPress
 * passes it when no permalink can be built. A filter result that is not an absolute http or https URL drops the
 * canonical link instead of emitting a broken one.
 *
 * @since 0.1.0
 */
class WordPressCanonicalUrlResolver implements CanonicalUrlResolverInterface
{
    public function __construct(private readonly CanonicalUrlResolverInterface $inner)
    {
    }

    public function resolve(PageContext $context): ?Url
    {
        $url = $this->inner->resolve($context);
        if (!$context->hasPost()) {
            return $url;
        }

        $post = get_post($context->objectId);
        if (!$post || 'publish' !== get_post_status($post)) {
            return $url;
        }

        return Url::tryFromString(apply_filters('get_canonical_url', $url?->toString() ?? false, $post));
    }
}
