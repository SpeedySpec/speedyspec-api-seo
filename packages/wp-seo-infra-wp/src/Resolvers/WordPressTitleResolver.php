<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Resolvers;

use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Builds the document title with the algorithm of {@link wp_get_document_title()} so sites see no change.
 *
 * The page context is ignored: WordPress decides the title from its global query conditionals and the `$page` and
 * `$paged` globals, and reading them here is what keeps the four legacy filters `pre_get_document_title`,
 * `document_title_separator`, `document_title_parts` and `document_title` firing in their original order with their
 * original arguments.
 *
 * Because this replaces the application title resolver, {@link SeoOverridesRepositoryInterface} is not consulted for
 * the title under WordPress in phase 1; this is revisited when WordPress-backed overrides arrive.
 *
 * @since 0.1.0
 */
class WordPressTitleResolver implements TitleResolverInterface
{
    public function resolve(PageContext $context): string
    {
        $title = apply_filters('pre_get_document_title', '');
        if (!empty($title)) {
            return $this->toString($title);
        }

        global $page, $paged;

        $title = [
            'title' => '',
        ];

        // If it's a 404 page, use a "Page not found" title.
        if (is_404()) {
            $title['title'] = __('Page not found');

            // If it's a search, use a dynamic search results title.
        } elseif (is_search()) {
            /* translators: %s: Search query. */
            $title['title'] = sprintf(__('Search Results for &#8220;%s&#8221;'), get_search_query());

            // If on the front page, use the site title.
        } elseif (is_front_page()) {
            $title['title'] = get_bloginfo('name', 'display');

            // If on a post type archive, use the post type archive title.
        } elseif (is_post_type_archive()) {
            $title['title'] = post_type_archive_title('', false);

            // If on a taxonomy archive, use the term title.
        } elseif (is_tax()) {
            $title['title'] = single_term_title('', false);

            /*
             * If we're on the blog page that is not the homepage
             * or a single post of any post type, use the post title.
             */
        } elseif (is_home() || is_singular()) {
            $title['title'] = single_post_title('', false);

            // If on a category or tag archive, use the term title.
        } elseif (is_category() || is_tag()) {
            $title['title'] = single_term_title('', false);

            // If on an author archive, use the author's display name.
        } elseif (is_author() && get_queried_object()) {
            $author = get_queried_object();
            $title['title'] = $author->display_name;

            // If it's a date archive, use the date as the title.
        } elseif (is_year()) {
            $title['title'] = get_the_date(_x('Y', 'yearly archives date format'));
        } elseif (is_month()) {
            $title['title'] = get_the_date(_x('F Y', 'monthly archives date format'));
        } elseif (is_day()) {
            $title['title'] = get_the_date();
        }

        // Add a page number if necessary.
        if (($paged >= 2 || $page >= 2) && !is_404()) {
            /* translators: %s: Page number. */
            $title['page'] = sprintf(__('Page %s'), max($paged, $page));
        }

        // Append the description or site title to give context.
        if (is_front_page()) {
            $title['tagline'] = get_bloginfo('description', 'display');
        } else {
            $title['site'] = get_bloginfo('name', 'display');
        }

        $sep = apply_filters('document_title_separator', '-');

        $title = apply_filters('document_title_parts', $title);

        $title = implode(" $sep ", array_filter($title));

        $title = apply_filters('document_title', $title);

        return $this->toString($title);
    }

    /**
     * Keeps the string return type when a legacy filter hands back another type, instead of failing the page.
     */
    private function toString(mixed $title): string
    {
        return is_scalar($title) ? (string) $title : '';
    }
}
