<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Computes a post's permalink exactly as {@link wp_get_canonical_url()} does, so canonical output does not change.
 *
 * The `get_canonical_url` filter is deliberately left out; {@link
 * \SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressCanonicalUrlResolver} applies it after overrides and the https
 * upgrade, where WordPress applied it last too.
 *
 * @since 0.1.0
 */
class WordPressPermalinkSource implements PermalinkSourceInterface
{
    public function permalinkFor(PageContext $context): ?Url
    {
        // Without an id, get_post() would fall back to the global post, which may not be the page described.
        if (!$context->hasPost()) {
            return null;
        }

        $post = get_post($context->objectId);
        if (!$post) {
            return null;
        }

        if ('publish' !== get_post_status($post)) {
            return null;
        }

        $canonicalUrl = get_permalink($post);

        // If a canonical is being generated for the current page, make sure it has pagination if needed.
        if (get_queried_object_id() === $post->ID) {
            $page = get_query_var('page', 0);
            if ($page >= 2) {
                if (!get_option('permalink_structure')) {
                    $canonicalUrl = add_query_arg('page', $page, $canonicalUrl);
                } else {
                    $canonicalUrl = trailingslashit($canonicalUrl) . user_trailingslashit($page, 'single_paged');
                }
            }

            $cpage = get_query_var('cpage', 0);
            if ($cpage) {
                $canonicalUrl = get_comments_pagenum_link($cpage);
            }
        }

        return Url::tryFromString($canonicalUrl);
    }
}
