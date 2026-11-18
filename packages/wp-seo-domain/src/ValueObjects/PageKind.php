<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

/**
 * Classifies the kind of page being rendered so SEO rules can target page types without WordPress conditionals.
 *
 * @since 0.1.0
 */
enum PageKind: string
{
    case Singular = 'singular';
    case FrontPage = 'front_page';
    case PostsIndex = 'posts_index';
    case TermArchive = 'term_archive';
    case AuthorArchive = 'author_archive';
    case DateArchive = 'date_archive';
    case PostTypeArchive = 'post_type_archive';
    case Search = 'search';
    case NotFound = 'not_found';
    case Other = 'other';
}
