<?php

declare(strict_types=1);

/**
 * SEO template functions: document title, robots meta tag and canonical link.
 *
 * Each function is a thin wrapper over the SEO contracts resolved from the shared service container, so any
 * implementation can be swapped without touching templates. Each resolves only its own part of the head, when it
 * runs, so the hooks behind it fire exactly where and when they always did. A contract that cannot be resolved makes
 * a function print nothing instead of failing.
 *
 * @package WordPress
 * @since 0.1.0 speedyspec-wp-seo-legacy
 */

use SpeedySpec\WP\Seo\Domain\Contracts\HeadRendererInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Legacy\HeadAccess;

/**
 * Returns document title for the current page.
 *
 * @since 4.4.0
 * @since 0.1.0 speedyspec-wp-seo-legacy Delegates to the bound title resolver; empty when it or the page context
 *              provider cannot be resolved. The result is display text that may contain HTML entities.
 *
 * @return string Tag with the document title.
 */
function wp_get_document_title(): string {
	return HeadAccess::title() ?? '';
}

/**
 * Displays title tag with content.
 *
 * @since 4.1.0
 * @since 4.4.0 Improved title output replaced `wp_title()`.
 * @since 0.1.0 speedyspec-wp-seo-legacy Prints the document title through the bound head renderer, which escapes it
 *              once without double-encoding; prints nothing when the renderer or the title cannot be resolved.
 * @access private
 */
function _wp_render_title_tag(): void {
	if ( ! current_theme_supports( 'title-tag' ) ) {
		return;
	}

	$renderer = HeadAccess::service( HeadRendererInterface::class );
	if ( ! $renderer instanceof HeadRendererInterface ) {
		return;
	}

	// The same resolution as wp_get_document_title(), but an unavailable title prints no tag instead of an empty one.
	$title = HeadAccess::title();
	if ( null === $title ) {
		return;
	}

	echo $renderer->renderTitle( $title );
}

/**
 * Displays the robots meta tag as necessary.
 *
 * Gathers robots directives to include for the current context, using the
 * {@see 'wp_robots'} filter. The directives are then sanitized, and the
 * robots meta tag is output if there is at least one relevant directive.
 *
 * @since 5.7.0
 * @since 5.7.1 No longer prevents specific directives to occur together.
 * @since 0.1.0 speedyspec-wp-seo-legacy Delegates to the bound robots directives resolver for the current page.
 */
function wp_robots(): void {
	$directives = HeadAccess::robots();
	if ( null === $directives || $directives->isEmpty() ) {
		return;
	}

	echo "<meta name='robots' content='" . esc_attr( $directives->toString() ) . "' />\n";
}

/**
 * Adds `noindex` to the robots meta tag if required by the site configuration.
 *
 * If a blog is marked as not being public then noindex will be output to
 * tell web robots not to index the page content. Add this to the
 * {@see 'wp_robots'} filter.
 *
 * Typical usage is as a {@see 'wp_robots'} callback:
 *
 *     add_filter( 'wp_robots', 'wp_robots_noindex' );
 *
 * @since 5.7.0
 *
 * @see wp_robots_no_robots()
 *
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function wp_robots_noindex( array $robots ) {
	if ( ! get_option( 'blog_public' ) ) {
		return wp_robots_no_robots( $robots );
	}

	return $robots;
}

/**
 * Adds `noindex` to the robots meta tag for embeds.
 *
 * Typical usage is as a {@see 'wp_robots'} callback:
 *
 *     add_filter( 'wp_robots', 'wp_robots_noindex_embeds' );
 *
 * @since 5.7.0
 *
 * @see wp_robots_no_robots()
 *
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function wp_robots_noindex_embeds( array $robots ) {
	if ( is_embed() ) {
		return wp_robots_no_robots( $robots );
	}

	return $robots;
}

/**
 * Adds `noindex` to the robots meta tag if a search is being performed.
 *
 * If a search is being performed then noindex will be output to
 * tell web robots not to index the page content. Add this to the
 * {@see 'wp_robots'} filter.
 *
 * Typical usage is as a {@see 'wp_robots'} callback:
 *
 *     add_filter( 'wp_robots', 'wp_robots_noindex_search' );
 *
 * @since 5.7.0
 *
 * @see wp_robots_no_robots()
 *
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function wp_robots_noindex_search( array $robots ) {
	if ( is_search() ) {
		return wp_robots_no_robots( $robots );
	}

	return $robots;
}

/**
 * Adds `noindex` to the robots meta tag.
 *
 * This directive tells web robots not to index the page content.
 *
 * Typical usage is as a {@see 'wp_robots'} callback:
 *
 *     add_filter( 'wp_robots', 'wp_robots_no_robots' );
 *
 * @since 5.7.0
 *
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function wp_robots_no_robots( array $robots ) {
	$robots['noindex'] = true;

	if ( get_option( 'blog_public' ) ) {
		$robots['follow'] = true;
	} else {
		$robots['nofollow'] = true;
	}

	return $robots;
}

/**
 * Adds `noindex` and `noarchive` to the robots meta tag.
 *
 * This directive tells web robots not to index or archive the page content and
 * is recommended to be used for sensitive pages.
 *
 * Typical usage is as a {@see 'wp_robots'} callback:
 *
 *     add_filter( 'wp_robots', 'wp_robots_sensitive_page' );
 *
 * @since 5.7.0
 *
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function wp_robots_sensitive_page( array $robots ) {
	$robots['noindex']   = true;
	$robots['noarchive'] = true;
	return $robots;
}

/**
 * Adds `max-image-preview:large` to the robots meta tag.
 *
 * This directive tells web robots that large image previews are allowed to be
 * displayed, e.g. in search engines, unless the blog is marked as not being public.
 *
 * Typical usage is as a {@see 'wp_robots'} callback:
 *
 *     add_filter( 'wp_robots', 'wp_robots_max_image_preview_large' );
 *
 * @since 5.7.0
 *
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function wp_robots_max_image_preview_large( array $robots ) {
	if ( get_option( 'blog_public' ) ) {
		$robots['max-image-preview'] = 'large';
	}
	return $robots;
}

/**
 * Returns the canonical URL for a post.
 *
 * When the post is the same as the current requested page the function will handle the
 * pagination arguments too.
 *
 * @since 4.6.0
 * @since 0.1.0 speedyspec-wp-seo-legacy Delegates to the bound canonical URL resolver; false when it cannot be
 *              resolved.
 *
 * @param int|WP_Post $post Optional. Post ID or object. Default is global `$post`.
 * @return string|false The canonical URL. False if the post does not exist
 *                      or has not been published yet.
 */
function wp_get_canonical_url( $post = null ): string|false {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	$id      = (int) $post->ID;
	$current = HeadAccess::context();

	// The current page's kind, pagination and locale only apply when the canonical is for the current post.
	if ( null !== $current && $current->hasPost() && $current->objectId === $id ) {
		$context = $current;
	} else {
		$context = new PageContext( PageKind::Singular, 'post', $id );
	}

	$url = HeadAccess::canonical( $context );

	return null === $url ? false : $url->toString();
}

/**
 * Outputs rel=canonical for singular queries.
 *
 * @since 2.9.0
 * @since 4.6.0 Adjusted to use `wp_get_canonical_url()`.
 * @since 0.1.0 speedyspec-wp-seo-legacy Prints for any page that carries a post, such as a static front page.
 */
function rel_canonical(): void {
	$context = HeadAccess::context();
	if ( null === $context || ! $context->hasPost() ) {
		return;
	}

	$url = wp_get_canonical_url( $context->objectId );

	if ( ! empty( $url ) ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
	}
}

/**
 * Prints the SEO head tags that no other template function prints.
 *
 * The title, robots meta tag and canonical link keep their own functions so themes and plugins that unhook them
 * still can; this prints everything else in the head document, in document order. Their contributors are not run
 * here, so their hooks fire only from their own functions; a tag of theirs added by another contributor is still
 * left out.
 *
 * @since 0.1.0 speedyspec-wp-seo-legacy
 */
function wp_seo_render_head(): void {
	$renderer = HeadAccess::service( HeadRendererInterface::class );
	if ( ! $renderer instanceof HeadRendererInterface ) {
		return;
	}

	$document = HeadAccess::extras();
	if ( null === $document ) {
		return;
	}

	foreach ( $document->metas() as $meta ) {
		if ( 'robots' !== $meta->key ) {
			echo $renderer->renderMeta( $meta );
		}
	}

	foreach ( $document->links() as $link ) {
		if ( 'canonical' !== $link->rel ) {
			echo $renderer->renderLink( $link );
		}
	}
}
