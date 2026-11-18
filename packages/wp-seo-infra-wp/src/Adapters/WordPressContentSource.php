<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\WP\Adapters;

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;

/**
 * Supplies page titles and summaries from WordPress content.
 *
 * Titles come from the template tags themes already use, so their filters keep applying. A summary built from the
 * post body is cut to an excerpt's length and cleaned of shortcodes, scripts and styles, so a description never
 * carries page furniture; a manual excerpt is the author's own text and is returned as stored. A password-protected
 * post has no summary, so its body never reaches anonymous visitors through the head.
 *
 * @since 0.1.0
 */
class WordPressContentSource implements ContentSourceInterface
{
    public function titleFor(PageContext $context): ?string
    {
        return match ($context->kind) {
            PageKind::Singular, PageKind::PostsIndex => single_post_title('', false),
            PageKind::TermArchive => single_term_title('', false),
            default => null,
        };
    }

    public function summaryFor(PageContext $context): ?string
    {
        // Without an id, get_post() would fall back to the global post, which may not be the page described.
        if (!$context->hasPost()) {
            return null;
        }

        $post = get_post($context->objectId);
        if (!$post || post_password_required($post)) {
            return null;
        }

        $excerpt = (string) ($post->post_excerpt ?? '');
        if ('' !== $excerpt) {
            return $excerpt;
        }

        return wp_trim_words(wp_strip_all_tags(strip_shortcodes((string) ($post->post_content ?? ''))), 55, '');
    }
}
