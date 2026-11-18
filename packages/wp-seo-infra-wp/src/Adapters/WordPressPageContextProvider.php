<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;

/**
 * Describes the current WordPress request as a page context so the SEO layer never reads query globals itself.
 *
 * Conditionals are checked from most to least specific, so a request matching several, such as a 404 that also
 * reports singular, gets the kind that matters for SEO. The post identity follows {@link is_singular()} alone, so a
 * static front page is a front page that still carries its post. Before the main query exists, as when
 * {@link wp_die()} prints the robots tag early, the request is described as another page without touching the query.
 *
 * @since 0.1.0
 */
class WordPressPageContextProvider implements PageContextProviderInterface
{
    public function current(): PageContext
    {
        if (!isset($GLOBALS['wp_query'])) {
            return new PageContext(PageKind::Other);
        }

        $kind = $this->kind();
        $objectId = get_queried_object_id();

        return new PageContext(
            $kind,
            match (true) {
                is_singular() => 'post',
                PageKind::TermArchive === $kind => 'term',
                PageKind::AuthorArchive === $kind => 'user',
                default => null,
            },
            0 === $objectId ? null : $objectId,
            max((int) get_query_var('paged', 0), (int) get_query_var('page', 0), 1),
            get_locale(),
            PageKind::NotFound === $kind ? 404 : 200,
        );
    }

    private function kind(): PageKind
    {
        return match (true) {
            is_404() => PageKind::NotFound,
            is_search() => PageKind::Search,
            is_front_page() => PageKind::FrontPage,
            is_home() => PageKind::PostsIndex,
            is_singular() => PageKind::Singular,
            is_post_type_archive() => PageKind::PostTypeArchive,
            is_category(), is_tag(), is_tax() => PageKind::TermArchive,
            is_author() => PageKind::AuthorArchive,
            is_date() => PageKind::DateArchive,
            default => PageKind::Other,
        };
    }
}
